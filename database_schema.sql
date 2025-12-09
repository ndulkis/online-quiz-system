-- Quiz System Database Schema and Sample Data

-- Create Database
CREATE DATABASE IF NOT EXISTS quiz_system;
USE quiz_system;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quizzes Table
CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    time_limit INT DEFAULT 10,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Questions Table
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_option CHAR(1),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Quiz Attempts Table
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT,
    time_taken INT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);

-- User Answers Table
CREATE TABLE IF NOT EXISTS user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option CHAR(1),
    is_correct BOOLEAN,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

-- Sample Users Data
INSERT INTO users (name, email, password) VALUES
('John Smith', 'john.smith@example.com', 'hashed_password_1'),
('Alex Johnson', 'alex.johnson@example.com', 'hashed_password_2'),
('Maria Garcia', 'maria.garcia@example.com', 'hashed_password_3'),
('David Chen', 'david.chen@example.com', 'hashed_password_4'),
('Sarah Williams', 'sarah.williams@example.com', 'hashed_password_5');

-- Sample Quizzes Data
INSERT INTO quizzes (title, description, category, time_limit, created_by) VALUES
('Web Development Quiz', 'Test your knowledge of HTML, CSS, and JavaScript', 'programming', 15, 2),
('Database Management', 'SQL and database fundamentals', 'programming', 20, 2),
('General Knowledge', 'Common facts about world history and culture', 'general', 10, 3),
('Science Quiz', 'Physics, Chemistry, and Biology fundamentals', 'science', 15, 4);

-- Sample Questions for Quiz 1 (Web Development)
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(1, 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Tech Modern Language', 'Hyper Transfer Markup Language', 'Home Tool Markup Language', 'A'),
(1, 'What does CSS stand for?', 'Creative Style Sheets', 'Cascading Style Sheets', 'Computer Style Sheets', 'Colorful Style Sheets', 'B'),
(1, 'Which language adds interactivity to web pages?', 'HTML', 'CSS', 'JavaScript', 'Python', 'C'),
(1, 'What is the correct HTML syntax for a hyperlink?', '<a href="url">Link</a>', '<link href="url">Link</link>', '<a url="url">Link</a>', '<href="url">Link</href>', 'A'),
(1, 'Which CSS property is used to change text color?', 'font-color', 'text-color', 'color', 'text-style', 'C');

-- Sample Questions for Quiz 2 (Database Management)
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(2, 'What does SQL stand for?', 'Structured Query Language', 'Standard Query Language', 'Simple Query Language', 'Special Query Language', 'A'),
(2, 'Which command is used to retrieve data?', 'UPDATE', 'SELECT', 'INSERT', 'DELETE', 'B'),
(2, 'What is a PRIMARY KEY?', 'A key used to open database', 'A unique identifier for a record', 'A key for password protection', 'A key to sort data', 'B'),
(2, 'Which is NOT a type of database?', 'Relational', 'NoSQL', 'Graph', 'Document', 'D');

-- Sample Questions for Quiz 3 (General Knowledge)
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(3, 'What is the capital of France?', 'London', 'Berlin', 'Paris', 'Madrid', 'C'),
(3, 'Which planet is closest to the sun?', 'Venus', 'Mercury', 'Earth', 'Mars', 'B'),
(3, 'In what year did World War II end?', '1943', '1944', '1945', '1946', 'C');

-- Sample Questions for Quiz 4 (Science)
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(4, 'What is the chemical symbol for Gold?', 'Gd', 'Au', 'Go', 'Gl', 'B'),
(4, 'How many bones are in the adult human body?', '196', '206', '216', '226', 'B'),
(4, 'What is the SI unit of force?', 'Kilogram', 'Newton', 'Joule', 'Watt', 'B');

-- Sample Quiz Attempts (for leaderboard testing)
INSERT INTO quiz_attempts (user_id, quiz_id, score, time_taken, completed_at) VALUES
(2, 1, 95, 480, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 1, 92, 520, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 1, 88, 600, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 1, 85, 720, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 1, 82, 750, DATE_SUB(NOW(), INTERVAL 1 DAY));
