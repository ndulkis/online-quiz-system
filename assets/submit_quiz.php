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

// Get the data sent from the frontend
$input = json_decode(file_get_contents('php://input'), true);
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$quiz_id = isset($input['quiz_id']) ? intval($input['quiz_id']) : 0;
$answers = isset($input['answers']) ? $input['answers'] : [];
$time_taken = isset($input['time_taken']) ? intval($input['time_taken']) : 0;

// Validate the required fields
if (empty($user_id) || empty($quiz_id) || empty($answers)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Get all the questions for this quiz
$stmt = $conn->prepare("SELECT id, correct_option FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate the score by comparing user answers to correct answers
$correct_count = 0;
$total_questions = count($questions);

foreach ($questions as $question) {
    $question_id = $question['id'];
    $correct_option = $question['correct_option'];

    // Check if the user answered this question
    if (isset($answers[$question_id])) {
        $user_answer = $answers[$question_id];

        // Compare the user's answer to the correct answer
        if ($user_answer === $correct_option) {
            $correct_count++;
        }
    }
}

// Calculate percentage score
$score = $total_questions > 0 ? round(($correct_count / $total_questions) * 100) : 0;

// Save the quiz attempt to the database
$stmt = $conn->prepare("
    INSERT INTO quiz_attempts (user_id, quiz_id, score, correct_answers, total_questions, time_taken)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$user_id, $quiz_id, $score, $correct_count, $total_questions, $time_taken]);

// Get the ID of the attempt we just created
$attempt_id = $conn->lastInsertId();

// Save each individual answer to the user_answers table
foreach ($questions as $question) {
    $question_id = $question['id'];
    $correct_option = $question['correct_option'];

    if (isset($answers[$question_id])) {
        $user_answer = $answers[$question_id];
        $is_correct = ($user_answer === $correct_option) ? 1 : 0;

        $stmt = $conn->prepare("
            INSERT INTO user_answers (attempt_id, question_id, selected_option, is_correct)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$attempt_id, $question_id, $user_answer, $is_correct]);
    }
}

// Send back the results
echo json_encode([
    'success' => true,
    'score' => $score,
    'correct' => $correct_count,
    'total' => $total_questions,
    'message' => 'Quiz submitted successfully!'
]);
?>
