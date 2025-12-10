<?php
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../config/db_connect.php';

// Connect to the database
$conn = getDbConnection();
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';

    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit;
    }

    // Query database for user using prepared statement
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and password matches
    if ($user && $user['password'] === $password) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
    }
} 
else if ($action === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = isset($input['name']) ? trim($input['name']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';
    $confirm = isset($input['confirm']) ? trim($input['confirm']) : '';

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }

    if ($password !== $confirm) {
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match'
        ]);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered'
        ]);
        exit;
    }

    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please login.',
        'user' => [
            'name' => $name,
            'email' => $email
        ]
    ]);
}
else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}
?>
