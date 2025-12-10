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

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (empty($user_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

// Get total quizzes taken
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$quizzesTaken = $result['total'];

// Get average score
$stmt = $conn->prepare("SELECT AVG(score) as avg_score FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$averageScore = $result['avg_score'] ? round($result['avg_score']) : 0;

// Get global rank (count how many users have a higher average score)
$stmt = $conn->prepare("
    SELECT COUNT(*) + 1 as user_rank
    FROM (
        SELECT user_id, AVG(score) as avg_score
        FROM quiz_attempts
        GROUP BY user_id
        HAVING AVG(score) > (
            SELECT AVG(score) FROM quiz_attempts WHERE user_id = ?
        )
    ) as better_users
");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$globalRank = $result ? $result['user_rank'] : 1;

echo json_encode([
    'success' => true,
    'stats' => [
        'quizzesTaken' => $quizzesTaken,
        'averageScore' => $averageScore . '%',
        'globalRank' => '#' . $globalRank
    ]
]);
?>
