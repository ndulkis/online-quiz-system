DATABASE SETUP - ONLINE QUIZ SYSTEM
====================================

QUICK START
-----------
To set up the entire database in one command:

mysql -u root < quiz_system.sql

This single file does everything:
- Creates the database
- Creates the user
- Creates all tables
- Adds sample data

DATABASE FILES
--------------
quiz_system.sql - Main database file (use this one!)
create_user.sql - Just creates the database user (optional, included in quiz_system.sql)
setup_database.sql - Just creates tables and data (optional, included in quiz_system.sql)

DATABASE INFORMATION
--------------------
Database Name: quiz_system
Database User: quiz_user
Password: (no password - for development only)
Host: localhost

DATABASE TABLES
---------------

1. users - Stores user account information
   - id: Unique user ID (auto-increment)
   - name: User's full name
   - email: User's email (must be unique)
   - password: User's password (plain text for development)
   - created_at: Account creation timestamp

2. quizzes - Stores quiz information
   - id: Unique quiz ID
   - title: Quiz title
   - description: Quiz description
   - category: Category (programming, science, general, etc.)
   - time_limit: Time limit in minutes
   - created_by: User ID of quiz creator
   - created_at: Quiz creation timestamp

3. questions - Stores quiz questions and answers
   - id: Unique question ID
   - quiz_id: Which quiz this belongs to
   - question_text: The actual question
   - option_a, option_b, option_c, option_d: Answer choices
   - correct_option: Correct answer (A, B, C, or D)

4. quiz_attempts - Stores each time a user takes a quiz
   - id: Unique attempt ID
   - user_id: Which user took the quiz
   - quiz_id: Which quiz was taken
   - score: Score achieved (percentage)
   - correct_answers: Number of correct answers
   - total_questions: Total number of questions
   - time_taken: Time taken in seconds
   - completed_at: Completion timestamp

5. user_answers - Stores individual answers for each question
   - id: Unique answer ID
   - attempt_id: Which quiz attempt this belongs to
   - question_id: Which question was answered
   - selected_option: User's selection (A, B, C, or D)
   - is_correct: Whether the answer was correct

TEST USER ACCOUNTS
------------------
You can login with these test accounts:

Name: John Smith     | Email: john@example.com  | Password: password123
Name: Maria Garcia   | Email: maria@example.com | Password: password123
Name: Alex Johnson   | Email: alex@example.com  | Password: password123
Name: Sarah Williams | Email: sarah@example.com | Password: password123

SAMPLE DATA
-----------
The database includes:
- 4 test users
- 4 sample quizzes (Web Development, Database, General Knowledge, Science)
- 15 questions total across all quizzes
- 6 sample quiz attempts for the leaderboard

RESETTING THE DATABASE
----------------------
To completely reset and start fresh:

mysql -u root < quiz_system.sql

This will delete all existing data and recreate everything from scratch.
