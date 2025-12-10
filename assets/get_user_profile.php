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

// Get the user ID from the URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Make sure we have a valid user ID
if (empty($user_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

// Get user information
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists
if (!$user) {
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    exit;
}

// Get total quizzes taken
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalQuizzes = $result['total'];

// Get average score
$stmt = $conn->prepare("SELECT AVG(score) as avg_score FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$avgScore = $result['avg_score'] ? round($result['avg_score']) : 0;

// Get highest score
$stmt = $conn->prepare("SELECT MAX(score) as max_score FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$maxScore = $result['max_score'] ? $result['max_score'] : 0;

// Calculate total time spent (in minutes)
$stmt = $conn->prepare("SELECT SUM(time_taken) as total_time FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalSeconds = $result['total_time'] ? $result['total_time'] : 0;
$totalMinutes = round($totalSeconds / 60);
$hours = floor($totalMinutes / 60);
$minutes = $totalMinutes % 60;
$timeSpent = $hours . 'h ' . $minutes . 'm';

// Format the created_at date
$memberSince = date('F Y', strtotime($user['created_at']));

// Send all the profile data back
echo json_encode([
    'success' => true,
    'profile' => [
        'name' => $user['name'],
        'email' => $user['email'],
        'memberSince' => $memberSince,
        'totalQuizzes' => $totalQuizzes,
        'averageScore' => $avgScore . '%',
        'highestScore' => $maxScore . '%',
        'timeSpent' => $timeSpent
    ]
]);
?>
