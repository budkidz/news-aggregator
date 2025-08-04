<?php
include 'config.php';

header('Content-Type: application/json');
// Allow CORS for public API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $sql = "SELECT title, description, url, image_url, source, published_at, category FROM articles";
    $params = [];

    if ($category) {
        $sql .= " WHERE category = ?";
        $params[] = $category;
    }
    $sql .= " ORDER BY published_at DESC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'count' => count($articles),
        'articles' => $articles
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
