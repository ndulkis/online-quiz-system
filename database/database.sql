CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);
CREATE TABLE quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  category VARCHAR(255),
  time_limit INT,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (created_by) REFRENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE TABLE quiz_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  quiz_id INT NOT NULL,
  score INT,
  Time_taken INT,
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

  FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  question_text TEXT NOT NULL,
  option_a VARCHAR(255) NOT NULL,
  option_b VARCHAR(255) NOT NULL,
  option_c VARCHAR(255) NOT NULL,
  option_d VARCHAR(255) NOT NULL,
  correct_option CHAR(1) NOT NULL CHECK (correct_option IN ('A', 'B', 'C', 'D')),

  FOREIGN KEY (quiz_id) REFRENCES quizzes(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
CREATE TABLE user_answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT NOT NULL,
  question_id INT NOT NULL,
  selected_option CHAR(1) CHECK(selected_option IN ('A', 'B', 'C', 'D')),
  is_correct BOOLEAN,

  FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
