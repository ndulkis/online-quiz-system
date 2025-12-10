<?php
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../config/db_connect.php';

// Connect to database
$conn = getDbConnection();
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Build SQL query based on category filter
if ($category !== 'all') {
    $stmt = $conn->prepare("SELECT id, title, description, category, time_limit as time,
                           (SELECT COUNT(*) FROM questions WHERE quiz_id = quizzes.id) as questions
                           FROM quizzes WHERE category = ?");
    $stmt->execute([$category]);
} else {
    $stmt = $conn->prepare("SELECT id, title, description, category, time_limit as time,
                           (SELECT COUNT(*) FROM questions WHERE quiz_id = quizzes.id) as questions
                           FROM quizzes");
    $stmt->execute();
}

$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($quizzes);
?>



