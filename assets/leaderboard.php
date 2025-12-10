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

// Get the period from the URL (global, monthly, weekly)
$period = isset($_GET['period']) ? $_GET['period'] : 'global';

// Build the SQL query based on the time period
$timeFilter = '';
if ($period === 'monthly') {
    // Only get attempts from the last 30 days
    $timeFilter = "AND qa.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} else if ($period === 'weekly') {
    // Only get attempts from the last 7 days
    $timeFilter = "AND qa.completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

// Get top users by average score
// We group by user and calculate their average score
$stmt = $conn->prepare("
    SELECT
        u.name,
        AVG(qa.score) as avg_score,
        COUNT(qa.id) as quiz_count
    FROM quiz_attempts qa
    JOIN users u ON qa.user_id = u.id
    WHERE 1=1 $timeFilter
    GROUP BY qa.user_id, u.name
    ORDER BY avg_score DESC, quiz_count DESC
    LIMIT 10
");
$stmt->execute();
$leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the data with rankings
$formattedLeaderboard = [];
$rank = 1;
foreach ($leaderboard as $entry) {
    $formattedLeaderboard[] = [
        'rank' => $rank,
        'name' => $entry['name'],
        'score' => round($entry['avg_score']) . '%',
        'quizzes' => $entry['quiz_count']
    ];
    $rank++;
}

// Send the data back as a JSON array (not wrapped in an object)
echo json_encode($formattedLeaderboard);
?>
