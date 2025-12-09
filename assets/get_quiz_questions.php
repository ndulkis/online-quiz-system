<?php
header('Content-Type: application/json');

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if (empty($quiz_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Quiz ID is required'
    ]);
    exit;
}

$mysql_path = "C:\\Program Files\\MySQL\\MySQL Server 9.5\\bin\\mysql.exe";

// Get quiz details
$quizQuery = "SELECT id, title, description, time_limit FROM quizzes WHERE id = $quiz_id LIMIT 1;";
$cmd = "\"$mysql_path\" -B -u quiz_user -D quiz_system -e \"$quizQuery\" 2>nul";
$quizResult = shell_exec($cmd);

// Parse quiz result
$quizLines = explode("\n", trim($quizResult));
$quiz = null;

if (count($quizLines) >= 2) {
    $quizData = trim($quizLines[1]);
    if (!empty($quizData)) {
        $parts = preg_split('/\t/', $quizData);
        if (count($parts) >= 3) {
            $quiz = [
                'id' => $parts[0],
                'title' => $parts[1],
                'description' => $parts[2],
                'time_limit' => isset($parts[3]) ? intval($parts[3]) : 10,
                'questions' => []
            ];
        }
    }
}

if (!$quiz) {
    echo json_encode([
        'success' => false,
        'message' => 'Quiz not found'
    ]);
    exit;
}

// Get questions for this quiz
$questionsQuery = "SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option FROM questions WHERE quiz_id = $quiz_id;";
$cmd = "\"$mysql_path\" -B -u quiz_user -D quiz_system -e \"$questionsQuery\" 2>nul";
$questionsResult = shell_exec($cmd);

$questionLines = explode("\n", trim($questionsResult));

// Skip header line and parse questions
for ($i = 1; $i < count($questionLines); $i++) {
    $line = trim($questionLines[$i]);
    if (empty($line)) continue;
    
    $parts = preg_split('/\t/', $line);
    if (count($parts) >= 7) {
        $correctOption = $parts[6]; // A, B, C, or D
        
        // Convert letter to index (A=0, B=1, C=2, D=3)
        $correctIndex = ord($correctOption) - ord('A');
        
        $question = [
            'id' => $parts[0],
            'text' => $parts[1],
            'options' => [
                $parts[2],  // option A
                $parts[3],  // option B
                $parts[4],  // option C
                $parts[5]   // option D
            ],
            'correct' => $correctIndex
        ];
        
        $quiz['questions'][] = $question;
    }
}

echo json_encode($quiz);
?>
