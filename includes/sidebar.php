<?php
require_once __DIR__ . '/../PHP/config.php';

// Fetch trending topics (top 8 categories by article count)
$stmt_trending = $pdo->query("
    SELECT category, COUNT(*) as article_count
    FROM articles
    GROUP BY category
    ORDER BY article_count DESC
    LIMIT 8
");
$trending_topics = $stmt_trending->fetchAll(PDO::FETCH_ASSOC);

// Fetch quick stats (robust queries)
// Count articles published today. Handles three common storage formats:
//  - MySQL DATE/DATETIME/TIMESTAMP strings (DATE(published_at) works)
//  - Unix epoch stored as integer (use FROM_UNIXTIME)
//  - Empty or NULL values are ignored
$stmt_today = $pdo->query(
    "SELECT COUNT(*) FROM articles WHERE ("
    . "  (published_at REGEXP '^[0-9]+$' AND DATE(FROM_UNIXTIME(published_at)) = CURDATE())"
    . "  OR (published_at REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}' AND DATE(published_at) = CURDATE())"
    . ")"
);
$articles_today = (int) $stmt_today->fetchColumn();

// Count distinct, non-empty source values (trimmed). NULL/empty strings are ignored.
$stmt_sources = $pdo->query("SELECT COUNT(DISTINCT NULLIF(TRIM(source), '')) FROM articles");
$sources_count = (int) $stmt_sources->fetchColumn();
?>

<aside class="sidebar">
    <h5><i class="bi bi-graph-up me-2"></i>Trending Topics</h5>
    <ul class="trending-list" id="trendingList">
        <?php if (empty($trending_topics)): ?>
            <li class="trending-item text-muted">No trending topics found.</li>
        <?php else: ?>
            <?php foreach ($trending_topics as $topic): ?>
                <li class="trending-item">
                    <a href="#" data-category="<?php echo htmlspecialchars(strtolower($topic['category'])); ?>">
                        # <?php echo htmlspecialchars(ucfirst($topic['category'])); ?>
                        <span class="badge bg-secondary float-end"><?php echo $topic['article_count']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <div class="mt-4 pt-3 border-top">
        <h6><i class="bi bi-bar-chart-line me-2"></i>Quick Stats</h6>
        <div class="d-flex justify-content-between mb-2">
            <span>Articles Today:</span><span class="fw-bold" id="articlesCount"><?php echo $articles_today; ?></span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Total Sources:</span><span class="fw-bold" id="sourcesCount"><?php echo $sources_count; ?></span>
        </div>
    </div>
</aside>

