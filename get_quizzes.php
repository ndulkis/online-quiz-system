<?php
require_once 'config.php';

// Get category filter from query parameter
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Build query
$query = "SELECT id, title, description, category, CAST(time_limit AS CHAR) as time, COUNT(q.id) as questions 
          FROM quizzes 
          LEFT JOIN questions q ON quizzes.id = q.quiz_id";

if ($category !== 'all') {
    $query .= " WHERE category = '" . $conn->real_escape_string($category) . "'";
}

$query .= " GROUP BY quizzes.id, quizzes.title, quizzes.description, quizzes.category, quizzes.time_limit
           ORDER BY quizzes.created_at DESC";

$result = $conn->query($query);

$quizzes = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
}

echo json_encode($quizzes);
$conn->close();
?>
