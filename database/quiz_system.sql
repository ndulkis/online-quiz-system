-- Online Quiz System Database
-- Complete database setup for the quiz application
-- Run this file to create the database, user, tables, and sample data

-- Create the database if it doesn't exist yet
CREATE DATABASE IF NOT EXISTS quiz_system;

-- Create a user called 'quiz_user' with no password
-- Note: In a real production app you'd want a strong password here
CREATE USER IF NOT EXISTS 'quiz_user'@'localhost';

-- Give quiz_user full access to the quiz_system database
-- This lets them read, write, update, and delete data
GRANT ALL PRIVILEGES ON quiz_system.* TO 'quiz_user'@'localhost';

-- Make sure the changes take effect
FLUSH PRIVILEGES;

-- Switch to using the quiz_system database
USE quiz_system;

-- Clean up old tables if they exist
-- We drop them in reverse order because of foreign key dependencies
DROP TABLE IF EXISTS user_answers;
DROP TABLE IF EXISTS quiz_attempts;
DROP TABLE IF EXISTS options;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS users;

-- Create the users table
-- This holds all user accounts for people taking quizzes
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the quizzes table
-- Stores info about each quiz like title, category, time limit
CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    time_limit INT DEFAULT 10,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create the questions table
-- Each quiz has multiple questions stored here
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Create the quiz_attempts table
-- Tracks every time someone takes a quiz
CREATE TABLE quiz_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    correct_answers INT NOT NULL,
    total_questions INT NOT NULL,
    time_taken INT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Create the user_answers table
-- Stores each individual answer a user gives during a quiz
CREATE TABLE user_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option CHAR(1) NOT NULL,
    is_correct BOOLEAN NOT NULL,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Add some test users so we can log in right away
INSERT INTO users (name, email, password) VALUES
('John Smith', 'john@example.com', 'password123'),
('Maria Garcia', 'maria@example.com', 'password123'),
('Alex Johnson', 'alex@example.com', 'password123'),
('Sarah Williams', 'sarah@example.com', 'password123');

-- Add a Web Development quiz
INSERT INTO quizzes (title, description, category, time_limit, created_by) VALUES
('Web Development Quiz', 'Test your knowledge of HTML, CSS, and JavaScript', 'programming', 15, 1);

-- Add questions for the Web Development quiz
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(1, 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Tech Modern Language', 'Hyper Transfer Markup Language', 'Home Tool Markup Language', 'A'),
(1, 'What does CSS stand for?', 'Creative Style Sheets', 'Cascading Style Sheets', 'Computer Style Sheets', 'Colorful Style Sheets', 'B'),
(1, 'Which language adds interactivity to web pages?', 'HTML', 'CSS', 'JavaScript', 'Python', 'C'),
(1, 'Which HTML tag is used for the largest heading?', '<h6>', '<heading>', '<h1>', '<head>', 'C'),
(1, 'How do you create a function in JavaScript?', 'function myFunction()', 'def myFunction()', 'create myFunction()', 'func myFunction()', 'A');

-- Add a Database quiz
INSERT INTO quizzes (title, description, category, time_limit, created_by) VALUES
('Database Management', 'SQL and database fundamentals', 'programming', 20, 1);

-- Add questions for the Database quiz
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(2, 'What does SQL stand for?', 'Structured Query Language', 'Simple Question Language', 'Server Query Language', 'System Quality Language', 'A'),
(2, 'Which SQL statement is used to retrieve data?', 'GET', 'SELECT', 'RETRIEVE', 'FETCH', 'B'),
(2, 'What is a primary key?', 'A key that opens locks', 'A unique identifier for a record', 'The first column in a table', 'A foreign reference', 'B'),
(2, 'Which command is used to remove a table?', 'DELETE TABLE', 'REMOVE TABLE', 'DROP TABLE', 'ERASE TABLE', 'C');

-- Add a General Knowledge quiz
INSERT INTO quizzes (title, description, category, time_limit, created_by) VALUES
('General Knowledge', 'Test your knowledge of world facts', 'general', 10, 2);

-- Add questions for the General Knowledge quiz
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(3, 'What is the capital of France?', 'London', 'Berlin', 'Paris', 'Madrid', 'C'),
(3, 'How many continents are there?', '5', '6', '7', '8', 'C'),
(3, 'Which planet is known as the Red Planet?', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'B');

-- Add a Science quiz
INSERT INTO quizzes (title, description, category, time_limit, created_by) VALUES
('Science Quiz', 'Physics, Chemistry, and Biology fundamentals', 'science', 15, 2);

-- Add questions for the Science quiz
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(4, 'What is the chemical symbol for water?', 'H2O', 'O2', 'CO2', 'H2', 'A'),
(4, 'What is the speed of light?', '300,000 km/s', '150,000 km/s', '450,000 km/s', '600,000 km/s', 'A'),
(4, 'What is the powerhouse of the cell?', 'Nucleus', 'Ribosome', 'Mitochondria', 'Chloroplast', 'C');

-- Add some sample quiz attempts for the leaderboard
INSERT INTO quiz_attempts (user_id, quiz_id, score, correct_answers, total_questions, time_taken) VALUES
(1, 1, 85, 17, 20, 750),
(2, 1, 92, 18, 20, 680),
(3, 1, 88, 18, 20, 720),
(1, 2, 72, 18, 25, 900),
(2, 2, 96, 24, 25, 850),
(4, 1, 78, 16, 20, 780);

-- Show confirmation that setup completed
SELECT 'Database setup completed successfully!' AS Message;
SELECT COUNT(*) AS 'Total Users' FROM users;
SELECT COUNT(*) AS 'Total Quizzes' FROM quizzes;
SELECT COUNT(*) AS 'Total Questions' FROM questions;
SELECT COUNT(*) AS 'Total Quiz Attempts' FROM quiz_attempts;
