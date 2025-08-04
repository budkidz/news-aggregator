<?php
include 'config.php';

// Example: RSS feed for technology news

$feeds = [
    // Zambian News
    'zambian' => [
        'http://times.co.zm/?feed=rss2',
        'https://lusakastar.com/feed',
        // Add more Zambian feeds here
    ],
    // World News
    'world' => [
        'http://www.africanews.com/feed/rss',
        // Add more world feeds here
    ],
    // Business News
    'business' => [
        'https://feeds.bbci.co.uk/news/business/rss.xml',
        'https://allafrica.com/tools/headlines/rdf/business/headlines.rdf',
        // Add more business feeds here
    ],
    // Technology News
    'technology' => [
        'https://feeds.bbci.co.uk/news/technology/rss.xml',
        'https://www.wired.com/feed/rss',
        // Add more technology feeds here
    ],
    // Sports News
    'sports' => [
        // Add sports feeds here
    ],
    // Health News
    'health' => [
        'https://www.sciencedaily.com/rss/health_medicine.xml',
        'https://www.nytimes.com/svc/collections/v1/publish/www.nytimes.com/section/health/rss.xml',
        // Add more health feeds here
    ]
];

foreach ($feeds as $category => $feedList) {
    foreach ($feedList as $feedUrl) {
        $rss = @simplexml_load_file($feedUrl);
        if ($rss === false) {
            echo "Failed to load feed: $feedUrl<br>";
            continue;
        }
        if (isset($rss->channel->item)) {
            foreach ($rss->channel->item as $item) {
                $title = (string)$item->title;
                $description = (string)$item->description;
                $url = (string)$item->link;
                $published_at = date('Y-m-d H:i:s', strtotime((string)$item->pubDate));
                $image_url = '';
                if (isset($item->enclosure) && isset($item->enclosure['url'])) {
                    $image_url = (string)$item->enclosure['url'];
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
}
echo "News fetched and stored.";