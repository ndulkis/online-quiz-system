## Simple Quiz (single-file PHP)

`assets/simple-quiz.php` is a self-contained PHP + SQLite quiz app with:
- User auth (register/login/logout via sessions)
- Quiz creation with dynamic questions
- Taking quizzes, auto-scoring, results, and leaderboard
- Seed data: 4 example quizzes auto-inserted when the DB is empty

### Requirements
- PHP 8+ with `pdo_sqlite` and `sqlite3` extensions enabled
- No web server needed beyond PHP’s built-in server

### Running locally
1. From the project root, start the PHP dev server using the PHP install that has SQLite enabled:
   ```bash
   php -S localhost:8000 -t assets
   ```
   If your PHP is not on PATH, use its full path (e.g. `"C:\Program Files\php-8.5.0\php.exe"`).
2. Open http://localhost:8000/simple-quiz.php in your browser.

### Database
- SQLite file: `assets/quiz.db`
- Auto-created on first request. Seed quizzes are inserted only if `quizzes` is empty.
- To reseed: stop the server, delete `assets/quiz.db`, restart, and reload the page.


### Common issues
- Missing SQLite driver: enable `extension=pdo_sqlite` and `extension=sqlite3` in `php.ini`, and set `extension_dir` to your PHP `ext` folder (e.g., `C:\Program Files\php-8.5.0\ext`).
- Seed not appearing: delete `assets/quiz.db` to let the seed repopulate.
