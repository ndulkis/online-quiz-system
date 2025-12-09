<?php
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$mysql_path = "C:\\Program Files\\MySQL\\MySQL Server 9.5\\bin\\mysql.exe";

// Function to query database
function queryDatabase($query) {
    global $mysql_path;
    $cmd = "\"$mysql_path\" -B -u quiz_user -D quiz_system -e \"$query\" 2>nul";
    $output = shell_exec($cmd);
    return $output;
}

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
    
    // Query database for user
    $query = "SELECT id, name, email, password FROM users WHERE email = '$email' LIMIT 1;";
    $result = queryDatabase($query);
    
    // Parse the result - split by newlines and get the data line (skip header)
    $lines = explode("\n", trim($result));
    $user = null;
    
    if (count($lines) >= 2) {
        // Get the data line (second line, first line is header)
        $dataLine = trim($lines[1]);
        if (!empty($dataLine)) {
            $parts = preg_split('/\s+/', $dataLine);
            if (count($parts) >= 4) {
                $userId = $parts[0];
                $userName = $parts[1];
                $userEmail = $parts[2];
                $userPassword = $parts[3];
                
                // Check password
                if ($userPassword === $password) {
                    $user = [
                        'id' => $userId,
                        'name' => $userName,
                        'email' => $userEmail
                    ];
                }
            }
        }
    }
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user
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
    $checkQuery = "SELECT id FROM users WHERE email = '$email';";
    $checkResult = queryDatabase($checkQuery);
    
    // Check if we got any results (more than just the header line)
    $resultLines = explode("\n", trim($checkResult));
    if (count($resultLines) > 1 && !empty(trim($resultLines[1]))) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered'
        ]);
        exit;
    }
    
    // Insert new user into database
    $insertQuery = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password');";
    queryDatabase($insertQuery);
    
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
