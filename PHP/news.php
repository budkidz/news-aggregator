<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Use output buffering to catch any stray output (like warnings) that could break JSON
ob_start();

require_once __DIR__ . '/config.php';

try {
    // --- Parameters ---
    $category = isset($_GET['category']) ? trim(strtolower($_GET['category'])) : 'all';
    $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

    // --- Build Query ---
    $sql = "SELECT id, title, description, url, image_url, source, published_at, category FROM articles";
    $params = [];
    $whereClauses = [];

    // 1. Category Filtering
    if ($category !== 'all') {
        // Handle category aliases if necessary, e.g., 'zambia' -> 'zambian'
        $aliasMap = ['zambia' => 'zambian'];
        $resolvedCategory = $aliasMap[$category] ?? $category;
        $whereClauses[] = "category = ?";
        $params[] = $resolvedCategory;
    }

    // 2. Search Filtering
    if (!empty($searchQuery)) {
        // Search across title, description, and source
        $whereClauses[] = "(title LIKE ? OR description LIKE ? OR source LIKE ?)";
        $searchTerm = "%{$searchQuery}%";
        // Add the search term for each placeholder
        array_push($params, $searchTerm, $searchTerm, $searchTerm);
    }

    // Combine WHERE clauses
    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    // 3. Ordering and Limiting
    // The LIMIT value must be an integer directly in the query, not a bound parameter.
    $sql .= " ORDER BY published_at DESC, id DESC LIMIT " . $limit;

    // --- Execute Query ---
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Clean and Send Response ---
    // Clear any buffered output before sending the JSON response
    ob_end_clean();

    echo json_encode([
        'status' => 'success',
        'count' => count($articles),
        'articles' => $articles
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Clear buffer on error as well
    ob_end_clean();
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
}

