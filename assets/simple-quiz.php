<?php
// Start or resume user session
session_start();

// Path to SQLite database file
$dbFile = __DIR__ . '/quiz.db';

// Create PDO connection to SQLite
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Enforce foreign key constraints
$pdo->exec('PRAGMA foreign_keys = ON');

// Create users table if it does not exist
$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

// Create quizzes table
$pdo->exec('CREATE TABLE IF NOT EXISTS quizzes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    category TEXT,
    time_limit INTEGER,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(created_by) REFERENCES users(id)
)');

// Create questions table
$pdo->exec('CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quiz_id INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct_option INTEGER NOT NULL,
    FOREIGN KEY(quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
)');

// Create quiz attempts table
$pdo->exec('CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    quiz_id INTEGER NOT NULL,
    score INTEGER NOT NULL,
    time_taken INTEGER DEFAULT 0,
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(quiz_id) REFERENCES quizzes(id)
)');

// Create user answers table
$pdo->exec('CREATE TABLE IF NOT EXISTS user_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    attempt_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    selected_option INTEGER NOT NULL,
    is_correct INTEGER NOT NULL,
    FOREIGN KEY(attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY(question_id) REFERENCES questions(id) ON DELETE CASCADE
)');

// Seed default quizzes and questions if database is empty
function seed_default_quizzes(PDO $pdo): void
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM quizzes')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $quizzes = [
        [
            'title' => 'Web Development Quiz',
            'description' => 'HTML, CSS, and JavaScript fundamentals.',
            'category' => 'programming',
            'time_limit' => 15,
            'questions' => [
                [
                    'text' => 'What does HTML stand for?',
                    'options' => ['Hyper Text Markup Language', 'High Tech Modern Language', 'Home Tool Markup Language', 'Hyperlinks and Text Markup Language'],
                    'correct' => 0,
                ],
                [
                    'text' => 'Which CSS property controls text size?',
                    'options' => ['font-size', 'text-style', 'font-weight', 'text-size'],
                    'correct' => 0,
                ],
                [
                    'text' => 'Which language adds interactivity to web pages?',
                    'options' => ['HTML', 'CSS', 'JavaScript', 'SQL'],
                    'correct' => 2,
                ],
            ],
        ],
        [
            'title' => 'Database Management',
            'description' => 'SQL basics and relational concepts.',
            'category' => 'programming',
            'time_limit' => 20,
            'questions' => [
                [
                    'text' => 'Which SQL command is used to remove rows?',
                    'options' => ['DELETE', 'DROP', 'REMOVE', 'TRUNCATE TABLE'],
                    'correct' => 0,
                ],
                [
                    'text' => 'A foreign key enforces what?',
                    'options' => ['Uniqueness', 'Referential integrity', 'Index order', 'Collation'],
                    'correct' => 1,
                ],
                [
                    'text' => 'Which join returns only matching rows between two tables?',
                    'options' => ['LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN', 'FULL JOIN'],
                    'correct' => 2,
                ],
            ],
        ],
        [
            'title' => 'General Knowledge',
            'description' => 'Short general trivia.',
            'category' => 'general',
            'time_limit' => 10,
            'questions' => [
                [
                    'text' => 'What is the capital of France?',
                    'options' => ['Paris', 'Berlin', 'Madrid', 'Rome'],
                    'correct' => 0,
                ],
                [
                    'text' => 'Which planet is known as the Red Planet?',
                    'options' => ['Venus', 'Mars', 'Jupiter', 'Mercury'],
                    'correct' => 1,
                ],
                [
                    'text' => 'How many continents are there?',
                    'options' => ['Five', 'Six', 'Seven', 'Eight'],
                    'correct' => 2,
                ],
            ],
        ],
        [
            'title' => 'Science Quiz',
            'description' => 'Basic science facts and concepts.',
            'category' => 'science',
            'time_limit' => 15,
            'questions' => [
                [
                    'text' => 'Water boils at what temperature at sea level?',
                    'options' => ['50°C', '75°C', '100°C', '150°C'],
                    'correct' => 2,
                ],
                [
                    'text' => 'What is the chemical symbol for gold?',
                    'options' => ['Ag', 'Au', 'Gd', 'Go'],
                    'correct' => 1,
                ],
                [
                    'text' => 'Which particle carries a negative charge?',
                    'options' => ['Proton', 'Neutron', 'Electron', 'Photon'],
                    'correct' => 2,
                ],
            ],
        ],
    ];

    $pdo->beginTransaction();
    try {
        $quizStmt = $pdo->prepare('INSERT INTO quizzes (title, description, category, time_limit, created_by) VALUES (?, ?, ?, ?, NULL)');
        $questionStmt = $pdo->prepare('INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)');

        foreach ($quizzes as $quiz) {
            $quizStmt->execute([$quiz['title'], $quiz['description'], $quiz['category'], $quiz['time_limit']]);
            $quizId = (int)$pdo->lastInsertId();
            foreach ($quiz['questions'] as $q) {
                $opts = $q['options'];
                $questionStmt->execute([$quizId, $q['text'], $opts[0], $opts[1], $opts[2], $opts[3], (int)$q['correct']]);
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Silent fail: seeding is optional
    }
}

// Seed on first run
seed_default_quizzes($pdo);

// Read JSON body and return as array
function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// Send JSON response and stop script
function respond($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Get currently logged in user or null
function current_user(PDO $pdo): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, name, email, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

// Require a logged in user or return 401
function require_auth(PDO $pdo): array
{
    $user = current_user($pdo);
    if (!$user) {
        respond(['error' => 'Authentication required'], 401);
    }
    return $user;
}

// Simple API router based on ?action=
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Register a new user
    if ($action === 'register') {
        $input = json_input();
        $name = trim($input['name'] ?? '');
        $email = strtolower(trim($input['email'] ?? ''));
        $password = $input['password'] ?? '';

        // Validate required fields
        if (!$name || !$email || !$password) {
            respond(['error' => 'Name, email, and password are required'], 400);
        }

        // Check if email already exists
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            respond(['error' => 'Email already registered'], 409);
        }

        // Hash password and insert user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);

        // Log the user in
        $_SESSION['user_id'] = (int)$pdo->lastInsertId();
        respond(['user' => current_user($pdo)]);
    }

    // Login existing user
    if ($action === 'login') {
        $input = json_input();
        $email = strtolower(trim($input['email'] ?? ''));
        $password = $input['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check credentials
        if (!$user || !password_verify($password, $user['password_hash'])) {
            respond(['error' => 'Invalid credentials'], 401);
        }

        // Store user id in session
        $_SESSION['user_id'] = (int)$user['id'];
        respond(['user' => current_user($pdo)]);
    }

    // Logout user
    if ($action === 'logout') {
        session_destroy();
        respond(['ok' => true]);
    }

    // Return current session user
    if ($action === 'session') {
        $user = current_user($pdo);
        respond(['user' => $user]);
    }

    // List quizzes (optionally by category)
    if ($action === 'quizzes') {
        $category = $_GET['category'] ?? null;
        if ($category && $category !== 'all') {
            $stmt = $pdo->prepare('SELECT q.*, (SELECT COUNT(*) FROM questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q WHERE category = ? ORDER BY q.created_at DESC');
            $stmt->execute([$category]);
        } else {
            $stmt = $pdo->query('SELECT q.*, (SELECT COUNT(*) FROM questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q ORDER BY q.created_at DESC');
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respond(['quizzes' => $rows]);
    }

    // Get single quiz with questions
    if ($action === 'quiz') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE id = ?');
        $stmt->execute([$id]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$quiz) {
            respond(['error' => 'Quiz not found'], 404);
        }

        // Fetch quiz questions without correct answers
        $qStmt = $pdo->prepare('SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE quiz_id = ? ORDER BY id ASC');
        $qStmt->execute([$id]);
        $questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);
        respond(['quiz' => $quiz, 'questions' => $questions]);
    }

    // Create a new quiz with questions
    if ($action === 'create_quiz') {
        $user = require_auth($pdo);
        $input = json_input();

        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $category = trim($input['category'] ?? '');
        $timeLimit = (int)($input['time_limit'] ?? 10);
        $questions = $input['questions'] ?? [];

        // Validate quiz data
        if (!$title || !$category || $timeLimit <= 0 || empty($questions)) {
            respond(['error' => 'Title, category, time limit, and at least one question are required'], 400);
        }

        // Validate each question
        foreach ($questions as $q) {
            if (!isset($q['text'], $q['options'], $q['correct']) || count($q['options']) < 4) {
                respond(['error' => 'Each question needs text, 4 options, and a correct index'], 400);
            }
        }

        // Use transaction for quiz + questions
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO quizzes (title, description, category, time_limit, created_by) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $category, $timeLimit, $user['id']]);
            $quizId = (int)$pdo->lastInsertId();

            $qStmt = $pdo->prepare('INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($questions as $q) {
                $opts = $q['options'];
                $qStmt->execute([$quizId, $q['text'], $opts[0], $opts[1], $opts[2], $opts[3], (int)$q['correct']]);
            }

            $pdo->commit();
            respond(['quiz_id' => $quizId]);
        } catch (Exception $e) {
            // Roll back on error
            $pdo->rollBack();
            respond(['error' => 'Failed to create quiz'], 500);
        }
    }

    // Submit quiz attempt and score it
    if ($action === 'attempt') {
        $user = require_auth($pdo);
        $input = json_input();

        $quizId = (int)($input['quiz_id'] ?? 0);
        $answers = $input['answers'] ?? [];
        $timeTaken = (int)($input['time_taken'] ?? 0);

        // Get correct answers for quiz
        $stmt = $pdo->prepare('SELECT id, correct_option FROM questions WHERE quiz_id = ?');
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$questions) {
            respond(['error' => 'Quiz not found or has no questions'], 404);
        }

        // Build map of question_id => correct_option
        $questionMap = [];
        foreach ($questions as $q) {
            $questionMap[$q['id']] = (int)$q['correct_option'];
        }

        // Count correct answers
        $correctCount = 0;
        foreach ($questionMap as $qid => $correctOption) {
            $selected = isset($answers[$qid]) ? (int)$answers[$qid] : -1;
            if ($selected === $correctOption) {
                $correctCount++;
            }
        }

        // Score as percentage
        $score = (int)round(($correctCount / count($questionMap)) * 100);

        // Save attempt and user answers
        $pdo->beginTransaction();
        try {
            // Insert attempt
            $aStmt = $pdo->prepare('INSERT INTO quiz_attempts (user_id, quiz_id, score, time_taken) VALUES (?, ?, ?, ?)');
            $aStmt->execute([$user['id'], $quizId, $score, $timeTaken]);
            $attemptId = (int)$pdo->lastInsertId();

            // Insert each answer
            $uaStmt = $pdo->prepare('INSERT INTO user_answers (attempt_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)');
            foreach ($questionMap as $qid => $correctOption) {
                $selected = isset($answers[$qid]) ? (int)$answers[$qid] : -1;
                $uaStmt->execute([$attemptId, $qid, $selected, (int)($selected === $correctOption)]);
            }

            $pdo->commit();
            respond(['attempt_id' => $attemptId, 'score' => $score, 'correct' => $correctCount, 'total' => count($questionMap)]);
        } catch (Exception $e) {
            // Roll back on error
            $pdo->rollBack();
            respond(['error' => 'Failed to save attempt'], 500);
        }
    }

    // Build leaderboard from attempts
    if ($action === 'leaderboard') {
        $stmt = $pdo->query('
            SELECT u.name, u.email, COUNT(qa.id) AS attempts, AVG(qa.score) AS avg_score, MAX(qa.score) AS best_score
            FROM quiz_attempts qa
            JOIN users u ON u.id = qa.user_id
            GROUP BY u.id
            HAVING attempts > 0
            ORDER BY avg_score DESC
            LIMIT 20
        ');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respond(['leaders' => $rows]);
    }

    // Fallback for unknown action
    respond(['error' => 'Unknown action'], 404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz System</title>
    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Global styles and reset */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f8f9fa; color: #333; }

        /* Layout container */
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }

        /* Header and navbar */
        header { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 0; }
        .navbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .logo { font-size: 24px; font-weight: bold; color: #4361ee; text-decoration: none; }
        .nav-links { display: flex; list-style: none; gap: 12px; flex-wrap: wrap; }
        .nav-links a { text-decoration: none; color: #333; font-weight: 500; padding: 8px 12px; border-radius: 5px; }
        .nav-links a:hover { background: #f0f2f5; }
        .nav-links a.active { background: #4361ee; color: white; }
        .user-menu { display: flex; align-items: center; gap: 10px; }

        /* Main content area */
        main { padding: 30px 0; min-height: 70vh; }

        /* Card component */
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }

        /* SPA page sections */
        .page { display: none; }
        .page.active { display: block; }

        /* Buttons */
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-primary { background: #4361ee; color: white; }
        .btn-success { background: #06d6a0; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-small { padding: 6px 12px; font-size: 14px; }
        .btn-ghost { background: transparent; border: 1px solid #ddd; color: #333; }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 15px; }
        .form-control:focus { outline: none; border-color: #4361ee; }
        .helper { color: #666; font-size: 13px; margin-top: 4px; }

        /* Quiz cards grid */
        .quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .quiz-card { transition: transform 0.2s; }
        .quiz-card:hover { transform: translateY(-3px); }
        .quiz-meta { color: #666; font-size: 14px; margin: 10px 0; }

        /* Small label badge */
        .badge { display: inline-block; padding: 6px 10px; border-radius: 20px; background: #e7f0ff; color: #4361ee; font-weight: 600; font-size: 12px; }

        /* Question and options */
        .question { margin: 20px 0; }
        .option { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; cursor: pointer; border: 2px solid transparent; }
        .option:hover { background: #e9ecef; }
        .option.selected { border-color: #4361ee; background: #e7f0ff; }

        /* Quiz navigation buttons */
        .quiz-nav { display: flex; justify-content: space-between; margin-top: 30px; gap: 10px; flex-wrap: wrap; }

        /* Tables (leaderboard, stats) */
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4361ee; color: white; }
        tr:hover { background: #f8f9fa; }

        /* Auth card container */
        .auth-container { max-width: 420px; margin: 50px auto; }
        .auth-tabs { display: flex; margin-bottom: 20px; }
        .auth-tab { flex: 1; text-align: center; padding: 15px; background: #f0f2f5; cursor: pointer; font-weight: 500; }
        .auth-tab.active { background: white; border-bottom: 3px solid #4361ee; }

        /* Footer styles */
        footer { background: #333; color: white; padding: 40px 0 20px; margin-top: 50px; }
        .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-bottom: 30px; }
        .footer-links a { color: #ddd; text-decoration: none; display: block; margin: 8px 0; }
        .footer-links a:hover { color: white; }
        .copyright { text-align: center; padding-top: 20px; border-top: 1px solid #444; color: #999; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar { flex-direction: column; align-items: flex-start; }
            .quiz-grid { grid-template-columns: 1fr; }
            .footer-content { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Top navigation bar -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">Quiz System</a>
                <ul class="nav-links">
                    <li><a href="#" onclick="showPage('dashboard')" class="active">Dashboard</a></li>
                    <li><a href="#" onclick="showPage('quizzes')">Quizzes</a></li>
                    <li><a href="#" onclick="showPage('leaderboard')">Leaderboard</a></li>
                    <li><a href="#" onclick="showPage('create')">Create Quiz</a></li>
                    <li><a href="#" onclick="showPage('profile')">Profile</a></li>
                </ul>
                <!-- User info / login button -->
                <div class="user-menu" id="user-menu">
                    <span id="user-label">Guest</span>
                    <a href="#" class="btn btn-primary btn-small" onclick="showPage('login')">Login</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main SPA content -->
    <main>
        <div class="container">
            <!-- Dashboard page -->
            <div id="dashboard" class="page active">
                <h1>Welcome to Quiz System</h1>
                <p>Test your knowledge and track your progress.</p>

                <!-- User stats card -->
                <div class="card">
                    <h2>Your Stats</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                        <div>
                            <h3 id="stat-quizzes">0</h3>
                            <p>Quizzes Taken</p>
                        </div>
                        <div>
                            <h3 id="stat-avg">0%</h3>
                            <p>Average Score</p>
                        </div>
                        <div>
                            <h3 id="stat-best">0%</h3>
                            <p>Best Score</p>
                        </div>
                    </div>
                </div>

                <!-- Recent quizzes on dashboard -->
                <div class="card">
                    <h2>Available Quizzes</h2>
                    <div class="quiz-grid" id="dashboard-quizzes"></div>
                </div>
            </div>

            <!-- Quizzes listing page -->
            <div id="quizzes" class="page">
                <h1>Available Quizzes</h1>
                <div class="card">
                    <!-- Simple category filter buttons -->
                    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                        <button class="btn btn-primary btn-small" onclick="filterQuizzes('all')">All</button>
                        <button class="btn btn-secondary btn-small" onclick="filterQuizzes('programming')">Programming</button>
                        <button class="btn btn-secondary btn-small" onclick="filterQuizzes('general')">General</button>
                        <button class="btn btn-secondary btn-small" onclick="filterQuizzes('science')">Science</button>
                        <button class="btn btn-secondary btn-small" onclick="filterQuizzes('mathematics')">Mathematics</button>
                    </div>
                    <!-- All quizzes go here -->
                    <div class="quiz-grid" id="quiz-list"></div>
                </div>
            </div>

            <!-- Leaderboard page -->
            <div id="leaderboard" class="page">
                <h1>Leaderboard</h1>
                <div class="card">
                    <table id="leaderboard-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Attempts</th>
                                <th>Avg Score</th>
                                <th>Best</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Create quiz page -->
            <div id="create" class="page">
                <h1>Create New Quiz</h1>
                <div class="card">
                    <form id="create-quiz-form" onsubmit="createQuiz(event)">
                        <!-- Quiz title -->
                        <div class="form-group">
                            <label for="quiz-title">Quiz Title</label>
                            <input type="text" id="quiz-title" class="form-control" required>
                        </div>
                        <!-- Quiz description -->
                        <div class="form-group">
                            <label for="quiz-description">Description</label>
                            <textarea id="quiz-description" class="form-control" rows="3"></textarea>
                        </div>
                        <!-- Quiz category -->
                        <div class="form-group">
                            <label for="quiz-category">Category</label>
                            <select id="quiz-category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="programming">Programming</option>
                                <option value="science">Science</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="general">General Knowledge</option>
                            </select>
                        </div>
                        <!-- Quiz time limit -->
                        <div class="form-group">
                            <label for="quiz-time">Time Limit (minutes)</label>
                            <input type="number" id="quiz-time" class="form-control" min="1" max="60" value="10">
                        </div>
                        <!-- Question builder -->
                        <div class="form-group">
                            <label>Questions</label>
                            <div id="question-builder"></div>
                            <button type="button" class="btn btn-ghost btn-small" onclick="addQuestion()">+ Add question</button>
                            <div class="helper">Each question needs 4 options and one correct answer.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Quiz</button>
                    </form>
                </div>
            </div>

            <!-- Profile page -->
            <div id="profile" class="page">
                <h1>Your Profile</h1>
                <div class="card" id="profile-card">
                    <p>Login to see your profile.</p>
                </div>
            </div>

            <!-- Login / Register page -->
            <div id="login" class="page">
                <div class="auth-container">
                    <div class="card">
                        <!-- Tabs to switch between login and register -->
                        <div class="auth-tabs">
                            <div class="auth-tab active" onclick="showAuthTab('login')">Login</div>
                            <div class="auth-tab" onclick="showAuthTab('register')">Register</div>
                        </div>
                        <!-- Login form -->
                        <form id="login-form" onsubmit="login(event)">
                            <div class="form-group">
                                <label for="login-email">Email</label>
                                <input type="email" id="login-email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="login-password">Password</label>
                                <input type="password" id="login-password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                        </form>
                        <!-- Register form -->
                        <form id="register-form" onsubmit="register(event)" style="display: none;">
                            <div class="form-group">
                                <label for="register-name">Full Name</label>
                                <input type="text" id="register-name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="register-email">Email</label>
                                <input type="email" id="register-email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="register-password">Password</label>
                                <input type="password" id="register-password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="register-confirm">Confirm Password</label>
                                <input type="password" id="register-confirm" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success" style="width: 100%;">Register</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quiz taking page -->
            <div id="quiz-taking" class="page">
                <div class="card">
                    <!-- Quiz header with timer -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 10px; flex-wrap: wrap;">
                        <div>
                            <h1 id="quiz-title">Quiz Title</h1>
                            <p>Question <span id="current-q">1</span> of <span id="total-q">1</span></p>
                        </div>
                        <div id="quiz-timer" style="background: #333; color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold;">
                            10:00
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="progress" style="height: 5px; background: #ddd; margin-bottom: 20px;">
                        <div id="progress-bar" style="height: 100%; background: #4361ee; width: 0%;"></div>
                    </div>

                    <!-- Question text -->
                    <div class="question">
                        <h2 id="question-text">Question text will appear here</h2>
                    </div>

                    <!-- Options container -->
                    <div id="options-container"></div>

                    <!-- Navigation buttons -->
                    <div class="quiz-nav">
                        <button class="btn btn-secondary" onclick="prevQuestion()">Previous</button>
                        <button class="btn btn-primary" onclick="nextQuestion()">Next</button>
                    </div>
                </div>
            </div>

            <!-- Results page -->
            <div id="results" class="page">
                <div class="card">
                    <div style="text-align: center;">
                        <h1>Quiz Completed!</h1>
                        <h2 id="quiz-results-title">Quiz Title</h2>
                        <!-- Overall score -->
                        <div style="font-size: 48px; color: #4361ee; margin: 20px 0;" id="score-display">0%</div>
                        <!-- Result stats -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin: 30px 0;">
                            <div>
                                <h3>Correct</h3>
                                <p id="correct-answers">0/0</p>
                            </div>
                            <div>
                                <h3>Time</h3>
                                <p id="time-taken">00:00</p>
                            </div>
                            <div>
                                <h3>Best Score</h3>
                                <p id="best-score">--</p>
                            </div>
                        </div>
                        <!-- Quick actions after quiz -->
                        <div style="margin-top: 30px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                            <button class="btn btn-primary" onclick="showPage('dashboard')">Dashboard</button>
                            <button class="btn btn-secondary" onclick="showPage('quizzes')">More Quizzes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer section -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    <h3>Quiz System</h3>
                    <p>A comprehensive platform for creating and taking quizzes.</p>
                </div>
                <div>
                    <h3>Quick Links</h3>
                    <div class="footer-links">
                        <a href="#" onclick="showPage('dashboard')">Dashboard</a>
                        <a href="#" onclick="showPage('quizzes')">Quizzes</a>
                        <a href="#" onclick="showPage('leaderboard')">Leaderboard</a>
                    </div>
                </div>
                <div>
                    <h3>Contact</h3>
                    <div class="footer-links">
                        <a href="mailto:support@quizsystem.com">support@quizsystem.com</a>
                        <a href="#">Help Center</a>
                        <a href="#">About Us</a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                &copy; 2025 Quiz System. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Helper to build API URL with action
        const endpoint = (action, extra = '') => `${window.location.pathname}?action=${action}${extra}`;

        // Global app state
        let state = {
            user: null,
            quizzes: [],
            currentQuiz: null,
            questions: [],
            answers: {},
            timer: null,
            timeRemaining: 0,
            quizStartedAt: null,
            leaderboard: []
        };

        // Wrapper for calling JSON API endpoints
        async function api(action, payload = null, method = 'POST') {
            const options = { method, headers: { 'Content-Type': 'application/json' } };
            if (payload !== null) options.body = JSON.stringify(payload);
            const res = await fetch(endpoint(action), options);
            const data = await res.json();
            if (!res.ok) {
                const msg = data.error || 'Request failed';
                throw new Error(msg);
            }
            return data;
        }

        // Show one page and hide others
        function showPage(pageName) {
            // Hide all pages
            document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
            // Reset nav link active state
            document.querySelectorAll('.nav-links a').forEach(link => link.classList.remove('active'));

            // Only highlight nav links for main pages
            if (pageName !== 'login' && pageName !== 'quiz-taking' && pageName !== 'results') {
                document.querySelector(`.nav-links a[onclick*="${pageName}"]`)?.classList.add('active');
            }

            // Show selected page
            document.getElementById(pageName).classList.add('active');

            // Lazy-load data when needed
            if (pageName === 'quizzes') {
                loadQuizzes();
            }
            if (pageName === 'leaderboard') {
                loadLeaderboard();
            }
        }

        // Switch between login and register forms
        function showAuthTab(tab) {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));

            if (tab === 'login') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                document.querySelector('.auth-tab:nth-child(1)').classList.add('active');
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                document.querySelector('.auth-tab:nth-child(2)').classList.add('active');
            }
        }

        // Initial page load setup
        async function bootstrap() {
            try {
                // Check existing session
                const session = await fetch(endpoint('session')).then(r => r.json());
                state.user = session.user || null;
                updateUserMenu();
            } catch (e) {
                console.warn(e);
            }

            // Load base data
            await loadQuizzes();
            await loadLeaderboard();
            updateStats();
            addQuestion();
        }

        // Update user menu (login/logout)
        function updateUserMenu() {
            const userMenu = document.getElementById('user-menu');
            userMenu.innerHTML = '';

            if (state.user) {
                // Show logged in user name and logout
                userMenu.innerHTML = `<span>${state.user.name}</span><a href="#" class="btn btn-primary btn-small" onclick="doLogout()">Logout</a>`;
            } else {
                // Show guest with login button
                userMenu.innerHTML = `<span>Guest</span><a href="#" class="btn btn-primary btn-small" onclick="showPage('login')">Login</a>`;
            }
        }

        // Handle login form submit
        async function login(event) {
            event.preventDefault();
            try {
                const data = await api('login', {
                    email: document.getElementById('login-email').value,
                    password: document.getElementById('login-password').value
                });
                state.user = data.user;
                updateUserMenu();
                showPage('dashboard');
                updateProfile();
            } catch (e) {
                alert(e.message);
            }
        }

        // Handle register form submit
        async function register(event) {
            event.preventDefault();
            const password = document.getElementById('register-password').value;
            const confirm = document.getElementById('register-confirm').value;

            // Simple password match check
            if (password !== confirm) {
                alert('Passwords do not match');
                return;
            }

            try {
                const data = await api('register', {
                    name: document.getElementById('register-name').value,
                    email: document.getElementById('register-email').value,
                    password
                });
                state.user = data.user;
                updateUserMenu();
                showPage('dashboard');
                updateProfile();
            } catch (e) {
                alert(e.message);
            }
        }

        // Logout current user
        async function doLogout() {
            await api('logout', {}, 'POST');
            state.user = null;
            updateUserMenu();
            updateProfile();
            showPage('login');
        }

        // Render quizzes into a card grid
        function renderQuizzes(targetId, quizzes) {
            const container = document.getElementById(targetId);
            if (!quizzes.length) {
                container.innerHTML = '<p>No quizzes yet. Create one!</p>';
                return;
            }

            // Build quiz cards
            container.innerHTML = quizzes.map(q => `
                <div class="quiz-card card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px; flex-wrap:wrap;">
                        <div>
                            <h3>${q.title}</h3>
                            <div class="quiz-meta">
                                Category: ${q.category || '�'} |
                                Questions: ${q.question_count || 0} |
                                Time: ${q.time_limit || 0} min
                            </div>
                            <p>${q.description || ''}</p>
                        </div>
                        <span class="badge">New</span>
                    </div>
                    <a href="#" class="btn btn-primary btn-small" onclick="startQuiz(${q.id})">Start Quiz</a>
                </div>
            `).join('');
        }

        // Load quizzes from server (optionally by category)
        async function loadQuizzes(category = 'all') {
            const url = category && category !== 'all' ? endpoint('quizzes', `&category=${category}`) : endpoint('quizzes');
            const res = await fetch(url);
            const data = await res.json();
            state.quizzes = data.quizzes || [];

            // Render quizzes on main list and dashboard preview
            renderQuizzes('quiz-list', state.quizzes);
            renderQuizzes('dashboard-quizzes', state.quizzes.slice(0, 3));
        }

        // Helper to filter quizzes by category
        function filterQuizzes(category) {
            loadQuizzes(category);
        }

        // Load leaderboard data
        async function loadLeaderboard() {
            const res = await fetch(endpoint('leaderboard'));
            const data = await res.json();
            state.leaderboard = data.leaders || [];
            const tbody = document.querySelector('#leaderboard-table tbody');

            if (!state.leaderboard.length) {
                tbody.innerHTML = '<tr><td colspan="5">No attempts yet.</td></tr>';
                return;
            }

            // Build leaderboard rows
            tbody.innerHTML = state.leaderboard.map((row, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${row.name}</td>
                    <td>${row.attempts}</td>
                    <td>${Math.round(row.avg_score)}%</td>
                    <td>${row.best_score}%</td>
                </tr>
            `).join('');
            updateStats();
        }

        // Update dashboard stats based on leaderboard
        function updateStats() {
            if (!state.user) return;
            const me = state.leaderboard.find(l => l.email === state.user.email);

            document.getElementById('stat-quizzes').textContent = me ? me.attempts : 0;
            document.getElementById('stat-avg').textContent = me ? `${Math.round(me.avg_score)}%` : '0%';
            document.getElementById('stat-best').textContent = me ? `${me.best_score}%` : '0%';
        }

        // Update profile card with user info and stats
        function updateProfile() {
            const card = document.getElementById('profile-card');
            if (!state.user) {
                card.innerHTML = '<p>Login to see your profile.</p>';
                return;
            }

            const me = state.leaderboard.find(l => l.email === state.user.email);
            const attempts = me ? me.attempts : 0;
            const avg = me ? Math.round(me.avg_score) : 0;
            const best = me ? me.best_score : 0;

            // Simple profile summary and stats
            card.innerHTML = `
                <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 20px;">
                    <div style="width: 80px; height: 80px; background: #4361ee; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                        ${state.user.name.substring(0,2).toUpperCase()}
                    </div>
                    <div>
                        <h2>${state.user.name}</h2>
                        <p>${state.user.email}</p>
                        <p>Member since: ${state.user.created_at}</p>
                    </div>
                </div>
                <h3>Performance Stats</h3>
                <table>
                    <tr><td>Total Quizzes Taken</td><td>${attempts}</td></tr>
                    <tr><td>Average Score</td><td>${avg}%</td></tr>
                    <tr><td>Best Score</td><td>${best}%</td></tr>
                </table>
            `;
        }

        // Add a new question block to the builder
        function addQuestion() {
            const container = document.getElementById('question-builder');
            const idx = container.children.length;
            const block = document.createElement('div');
            block.className = 'card';
            block.style.marginBottom = '12px';

            // Single question editor block
            block.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                    <h4>Question ${idx + 1}</h4>
                    <button type="button" class="btn btn-ghost btn-small" onclick="this.parentElement.parentElement.remove()">Remove</button>
                </div>
                <div class="form-group">
                    <label>Question text</label>
                    <input type="text" class="form-control question-text" required>
                </div>
                ${['A','B','C','D'].map((label, i) => `
                    <div class="form-group">
                        <label>Option ${label}</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="radio" name="correct-${idx}" value="${i}" ${i === 0 ? 'checked' : ''}>
                            <input type="text" class="form-control option-${i}" placeholder="Answer ${label}" required>
                        </div>
                    </div>
                `).join('')}
            `;
            container.appendChild(block);
        }

        // Read all questions from builder UI
        function readQuestionsFromBuilder() {
            const blocks = Array.from(document.querySelectorAll('#question-builder .card'));
            return blocks.map((block, idx) => {
                const text = block.querySelector('.question-text').value.trim();
                const options = Array.from(block.querySelectorAll('input[type="text"].form-control')).map(el => el.value.trim());
                const correct = parseInt(block.querySelector('input[type="radio"]:checked').value, 10);

                if (!text || options.some(o => !o)) throw new Error(`Question ${idx + 1} is incomplete`);
                return { text, options, correct };
            });
        }

        // Submit quiz creation form
        async function createQuiz(event) {
            event.preventDefault();
            if (!state.user) {
                alert('Login first');
                showPage('login');
                return;
            }

            let questions = [];
            try {
                // Extract questions from UI
                questions = readQuestionsFromBuilder();
            } catch (e) {
                alert(e.message);
                return;
            }

            try {
                // Send create_quiz API request
                await api('create_quiz', {
                    title: document.getElementById('quiz-title').value,
                    description: document.getElementById('quiz-description').value,
                    category: document.getElementById('quiz-category').value,
                    time_limit: parseInt(document.getElementById('quiz-time').value, 10),
                    questions
                });
                alert('Quiz created');
                // Reset form and builder
                document.getElementById('create-quiz-form').reset();
                document.getElementById('question-builder').innerHTML = '';
                addQuestion();
                await loadQuizzes();
                showPage('quizzes');
            } catch (e) {
                alert(e.message);
            }
        }

        // Start a quiz by id
        async function startQuiz(id) {
            if (!state.user) {
                alert('Login to take quizzes');
                showPage('login');
                return;
            }

            // Fetch quiz and questions
            const res = await fetch(endpoint('quiz', `&id=${id}`));
            const data = await res.json();
            state.currentQuiz = data.quiz;
            state.questions = data.questions || [];
            state.answers = {};
            state.currentIndex = 0;

            // Setup timer values
            state.timeRemaining = (state.currentQuiz.time_limit || 10) * 60;
            state.quizStartedAt = Date.now();

            // Fill quiz header
            document.getElementById('quiz-title').textContent = state.currentQuiz.title;
            document.getElementById('quiz-results-title').textContent = state.currentQuiz.title;
            document.getElementById('total-q').textContent = state.questions.length;

            // Load first question and start timer
            loadQuestion();
            startTimer();
            showPage('quiz-taking');
        }

        // Load current question into UI
        function loadQuestion() {
            const question = state.questions[state.currentIndex];
            if (!question) return;

            // Update question number and progress bar
            document.getElementById('current-q').textContent = state.currentIndex + 1;
            const progress = ((state.currentIndex + 1) / state.questions.length) * 100;
            document.getElementById('progress-bar').style.width = `${progress}%`;

            // Show question text
            document.getElementById('question-text').textContent = question.question_text;

            // Build options list
            const options = [question.option_a, question.option_b, question.option_c, question.option_d];
            const container = document.getElementById('options-container');
            container.innerHTML = '';

            options.forEach((opt, idx) => {
                const div = document.createElement('div');
                div.className = 'option';
                div.textContent = opt;
                div.onclick = () => selectOption(div, idx, question.id);

                // Restore previous choice if any
                if (state.answers[question.id] === idx) {
                    div.classList.add('selected');
                }
                container.appendChild(div);
            });
        }

        // Handle selecting an option
        function selectOption(element, index, questionId) {
            // Clear other selections
            document.querySelectorAll('#options-container .option').forEach(opt => opt.classList.remove('selected'));
            element.classList.add('selected');
            state.answers[questionId] = index;
        }

        // Go to previous question
        function prevQuestion() {
            if (state.currentIndex > 0) {
                state.currentIndex--;
                loadQuestion();
            }
        }

        // Go to next question or submit at end
        function nextQuestion() {
            if (state.currentIndex < state.questions.length - 1) {
                state.currentIndex++;
                loadQuestion();
            } else {
                // Last question -> submit
                submitQuiz();
            }
        }

        // Start countdown timer for quiz
        function startTimer() {
            clearInterval(state.timer);
            const timerElement = document.getElementById('quiz-timer');

            state.timer = setInterval(() => {
                const minutes = Math.floor(state.timeRemaining / 60);
                const seconds = state.timeRemaining % 60;

                timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                if (state.timeRemaining <= 0) {
                    // Time up -> auto submit
                    clearInterval(state.timer);
                    submitQuiz();
                }

                state.timeRemaining--;
            }, 1000);
        }

        // Submit quiz answers to server
        async function submitQuiz() {
            clearInterval(state.timer);
            const elapsed = Math.floor((Date.now() - state.quizStartedAt) / 1000);

            try {
                // Send attempt API with answers
                const result = await api('attempt', {
                    quiz_id: state.currentQuiz.id,
                    answers: state.answers,
                    time_taken: elapsed
                });

                // Fill results page
                document.getElementById('score-display').textContent = `${result.score}%`;
                document.getElementById('correct-answers').textContent = `${result.correct}/${result.total}`;
                document.getElementById('time-taken').textContent = formatSeconds(elapsed);

                // Refresh leaderboard to show new stats
                await loadLeaderboard();
                const me = state.leaderboard.find(l => l.email === state.user.email);
                document.getElementById('best-score').textContent = me ? `${me.best_score}%` : '--';

                // Show results page
                showPage('results');
            } catch (e) {
                alert(e.message);
            }
        }

        // Convert seconds to MM:SS
        function formatSeconds(total) {
            const m = Math.floor(total / 60);
            const s = total % 60;
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }

        // Run bootstrap when script loads
        bootstrap();
    </script>
</body>
</html>
