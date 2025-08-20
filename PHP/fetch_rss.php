<?php
include 'config.php';

// Test database connection
try {
    $pdo->query("SELECT 1");
    if (PHP_SAPI === 'cli') {
        echo "Database connection successful.\n";
    }
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Truncate articles table before fetching new articles
try {
    $pdo->exec("TRUNCATE TABLE articles");
    if (PHP_SAPI === 'cli') {
        echo "Successfully truncated articles table.\n";
    }
} catch (PDOException $e) {
    echo "Error truncating table: " . $e->getMessage() . "\n";
    exit;
}

function http_get($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36 NewsBiteBot/1.0',
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
                'Accept: application/rss+xml, application/xml;q=0.9, */*;q=0.8',
                'Accept-Language: en-US,en;q=0.9'
            ],
        ]);
        $data = curl_exec($ch);
        if ($data === false) {
            $err = curl_error($ch);
            if (PHP_SAPI === 'cli') {
                echo "cURL error for $url: $err\n";
            }
            error_log('cURL error fetching ' . $url . ': ' . $err);
            curl_close($ch);
            return false;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 400) {
            if (PHP_SAPI === 'cli') {
                echo "HTTP error $code for $url\n";
            }
            error_log('HTTP ' . $code . ' fetching ' . $url);
            return false;
        }
        return $data;
    } else {
        // Fallback for environments without cURL
        $context = stream_context_create(['http' => ['method' => 'GET', 'header' => "User-Agent: Mozilla/5.0\r\n", 'timeout' => 20]]);
        return @file_get_contents($url, false, $context);
    }
}

function scrapeImageFromPage($url) {
    $html = http_get($url);
    if ($html === false) return '';

    $doc = new DOMDocument();
    @$doc->loadHTML($html);

    $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);

    foreach ($doc->getElementsByTagName('meta') as $meta) {
        $img = '';
        $prop = strtolower($meta->getAttribute('property'));
        $name = strtolower($meta->getAttribute('name'));
        if (($prop === 'og:image' || $prop === 'og:image:secure_url' || $name === 'twitter:image' || $name === 'twitter:image:src') && $meta->getAttribute('content')) {
            $img = $meta->getAttribute('content');
        }
        if ($img) {
            if (strpos($img, 'http') !== 0) {
                $img = rtrim($baseUrl, '/') . '/' . ltrim($img, '/');
            }
            return $img;
        }
    }
    return '';
}

// RSS feeds for news

$feeds = [
    // Zambian News
    'zambian' => [
        'https://diggers.news/feed',
        'https://www.znbc.co.zm/news/feed/',
        'https://zambianbusinesstimes.com/feed/',
        'https://lusakastar.com/feed/',
        'https://www.dailynationzambia.com/feed/',
        'https://www.miningnewszambia.com/feed/',
    ],
    // World News
    'world' => [
        'https://www.africanews.com/feed/rss',
        'https://feeds.bbci.co.uk/news/world/rss.xml',
    ],
    // Business News
    'business' => [
        'https://feeds.bbci.co.uk/news/business/rss.xml',
        'https://allafrica.com/tools/headlines/rdf/business/headlines.rdf',
    ],
    // Technology News
    'technology' => [
        'https://feeds.bbci.co.uk/news/technology/rss.xml',
        'https://www.wired.com/feed/rss',
    ],
    // Sports News
    'sports' => [
        'http://feeds.bbci.co.uk/sport/rss.xml',
        'http://www.espn.com/espn/rss/news',
        'https://allafrica.com/tools/headlines/rdf/sport/headlines.rdf',
    ],
    // Health News
    'health' => [
        'https://www.sciencedaily.com/rss/health_medicine.xml',
        'https://www.nytimes.com/svc/collections/v1/publish/www.nytimes.com/section/health/rss.xml',
    ]
];

foreach ($feeds as $category => $feedList) {
    foreach ($feedList as $feedUrl) {
        $feedContent = http_get($feedUrl);
        if ($feedContent === false) {
            // Error is already logged and printed in http_get
            continue;
        }
        if (str_contains($feedUrl, 'nyt.com/svc/collections')) {
            $json = json_decode($feedContent, true);
            foreach ($json['members'] ?? [] as $item) {
                $title        = $item['title'] ?? ($item['summary'] ?? '');
                $description  = $item['summary'] ?? '';
                $url          = $item['url'] ?? '';
                if (!$url) continue;
                $updatedRaw   = $item['updated_date'] ?? '';
                $ts = strtotime($updatedRaw);
                if ($ts === false) { $ts = time(); }
                $published_at = date('Y-m-d H:i:s', $ts);
                $image_url    = '';
                if (!empty($item['multimedia']) && is_array($item['multimedia'])) {
                    foreach ($item['multimedia'] as $media) {
                        if (!empty($media['url'])) { $image_url = $media['url']; break; }
                    }
                }
                $source = parse_url($url, PHP_URL_HOST);
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE url = ?");
                $stmt->execute([$url]);
                if ($stmt->fetchColumn() == 0) {
                    $insert = $pdo->prepare("INSERT INTO articles (title, description, url, image_url, source, published_at, category)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $insert->execute([$title, strip_tags($description), $url, $image_url, $source, $published_at, $category]);
                }
            }
            continue; // handled JSON format
        }
        libxml_use_internal_errors(true);
        $rss = simplexml_load_string($feedContent);
        if ($rss === false) {
            if (PHP_SAPI === 'cli') {
                echo "Failed to parse XML for feed: $feedUrl\n";
            }
            error_log('XML parse errors for feed ' . $feedUrl . ': ' . json_encode(libxml_get_errors()));
            libxml_clear_errors();
            continue;
        }
        libxml_clear_errors();

        $items = isset($rss->channel->item) ? $rss->channel->item : (isset($rss->entry) ? $rss->entry : []);

        foreach ($items as $item) {
            $title = (string)$item->title;
            $description = (string)$item->description;
            if (empty($description)) {
                $description = isset($item->summary) ? (string)$item->summary : '';
            }
            // Clean description: strip HTML tags
            $description = strip_tags($description);
            $url = isset($item->link['href']) ? (string)$item->link['href'] : (string)$item->link;
            $published_at_str = isset($item->pubDate) ? (string)$item->pubDate : (isset($item->updated) ? (string)$item->updated : 'now');
            $published_at = date('Y-m-d H:i:s', strtotime($published_at_str));
            
            $image_url = '';
            if (isset($item->enclosure) && isset($item->enclosure['url'])) {
                $image_url = (string)$item->enclosure['url'];
            }
            if (!$image_url) {
                $image_url = scrapeImageFromPage($url);
            }
            $namespaces = $item->getNameSpaces(true);
            if (isset($namespaces['media'])) {
                $media = $item->children($namespaces['media']);
                if (isset($media->content) && $media->content->attributes()->url) {
                    $image_url = (string)$media->content->attributes()->url;
                } elseif (isset($media->thumbnail) && $media->thumbnail->attributes()->url) {
                    $image_url = (string)$media->thumbnail->attributes()->url;
                }
            }
            $source = parse_url($url, PHP_URL_HOST);

            // Check if article already exists (by URL)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE url = ?");
            $stmt->execute([$url]);
            if ($stmt->fetchColumn() == 0) {
                $insert = $pdo->prepare("INSERT INTO articles (title, description, url, image_url, source, published_at, category)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert->execute([$title, $description, $url, $image_url, $source, $published_at, $category]);
            }
        }
    }
}
echo "News fetching process completed.\n";