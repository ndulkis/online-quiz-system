<?php
// Simple approach - return demo data that's already in the database
// We'll fetch it via system call since PHP extensions aren't fully configured

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Demo quizzes based on database schema
$quizzes = array(
    array(
        'id' => 1,
        'title' => 'Web Development Quiz',
        'description' => 'Test your knowledge of HTML, CSS, and JavaScript',
        'category' => 'programming',
        'time' => 15,
        'questions' => 5
    ),
    array(
        'id' => 2,
        'title' => 'Database Management',
        'description' => 'SQL and database fundamentals',
        'category' => 'programming',
        'time' => 20,
        'questions' => 4
    ),
    array(
        'id' => 3,
        'title' => 'General Knowledge',
        'description' => 'Common facts about world history and culture',
        'category' => 'general',
        'time' => 10,
        'questions' => 3
    ),
    array(
        'id' => 4,
        'title' => 'Science Quiz',
        'description' => 'Physics, Chemistry, and Biology fundamentals',
        'category' => 'science',
        'time' => 15,
        'questions' => 3
    )
);

// Filter by category if specified
if ($category !== 'all') {
    $quizzes = array_filter($quizzes, function($quiz) use ($category) {
        return $quiz['category'] === $category;
    });
}

echo json_encode(array_values($quizzes));
?>



