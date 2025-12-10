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

// Get the user's recent quiz attempts along with quiz details
// We join quiz_attempts with quizzes to get quiz info
$stmt = $conn->prepare("
    SELECT
        q.id as quiz_id,
        q.title,
        q.description,
        qa.score,
        (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as total_questions
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.user_id = ?
    ORDER BY qa.completed_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the data for the frontend to match what it expects
$formattedActivities = [];
foreach ($activities as $activity) {
    $formattedActivities[] = [
        'quiz_id' => $activity['quiz_id'],
        'quiz_title' => $activity['title'],
        'quiz_description' => $activity['description'],
        'score' => $activity['score'],
        'questions_count' => $activity['total_questions']
    ];
}

// Send the data back as a JSON array (not wrapped in an object)
echo json_encode($formattedActivities);
?>
