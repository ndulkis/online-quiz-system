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

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if (empty($quiz_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Quiz ID is required'
    ]);
    exit;
}

// Get quiz details
$stmt = $conn->prepare("SELECT id, title, description, time_limit FROM quizzes WHERE id = ? LIMIT 1");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    echo json_encode([
        'success' => false,
        'message' => 'Quiz not found'
    ]);
    exit;
}

// Get questions for this quiz
$stmt = $conn->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the questions for the frontend
$quiz['questions'] = [];
foreach ($questions as $question) {
    // Convert letter to index (A=0, B=1, C=2, D=3)
    $correctIndex = ord($question['correct_option']) - ord('A');

    $quiz['questions'][] = [
        'id' => $question['id'],
        'text' => $question['question_text'],
        'options' => [
            $question['option_a'],
            $question['option_b'],
            $question['option_c'],
            $question['option_d']
        ],
        'correct' => $correctIndex
    ];
}

echo json_encode($quiz);
?>
