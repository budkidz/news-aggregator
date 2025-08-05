<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Use PDO instead of mysqli for better compatibility
include 'config.php';

try {
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $sql = "SELECT * FROM articles";
    $params = [];

    if ($category && $category !== 'all') {
        $sql .= " WHERE category = ?";
        $params[] = $category;
    }
    $sql .= " ORDER BY id DESC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($articles);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);

    // Format the response to match frontend expectations
    $formattedArticles = [];
    foreach ($articles as $row) {
        $formattedArticles[] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'url' => $row['url'],
            'image_url' => $row['image_url'] ?: 'https://via.placeholder.com/300x150',
            'source' => $row['source'],
            'published_at' => $row['published_at'],
            'category' => $row['category']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($formattedArticles),
        'articles' => $formattedArticles
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'articles' => []
    ]);
}
?>
