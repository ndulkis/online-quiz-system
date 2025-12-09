# Quiz System Project - Setup Checklist

## ✓ What We've Already Done
- [x] Cloned the repository
- [x] Installed VS Code extensions (MySQL Client, SQLTools, Live Server)
- [x] PHP 8.4.14 is installed
- [x] PHP has MySQLi extension enabled
- [x] Created `config.php` for database connection
- [x] Created `get_quizzes.php` to fetch quizzes from database
- [x] Created `database_schema.sql` with all tables and sample data
- [x] Updated `simple quiz.html` to use database for quizzes

## ⚠️ What You Need to Do Now

### Step 1: Install MySQL (Choose ONE option)
**Option A: MySQL Community Server** (Most common for school projects)
- Download: https://dev.mysql.com/downloads/mysql/
- Choose version 8.0 or 5.7
- Install with default settings
- Set root password during installation

**Option B: MariaDB** (MySQL-compatible, easier to install)
- Download: https://mariadb.org/download/
- Install with default settings
- Set root password during installation

**Option C: Already have MySQL installed?**
- Skip to Step 2

### Step 2: Create the Database
After MySQL is installed and running:

1. Open Command Prompt or PowerShell
2. Run:
```
mysql -u root -p < "C:\Users\ricjo\OneDrive\Desktop\CPSC 332 Project\online-quiz-system\database_schema.sql"
```
3. Enter your MySQL root password when prompted

Or manually:
1. Connect to MySQL: `mysql -u root -p`
2. Copy content from `database_schema.sql`
3. Paste into MySQL client

### Step 3: Update config.php
Edit the file: `config.php`

Update the password to match what you set during MySQL installation:
```php
define('DB_PASS', 'YOUR_MYSQL_PASSWORD');
```

### Step 4: Run PHP Server
1. Open terminal in project folder
2. Run:
```
php -S localhost:8000
```

3. Visit: http://localhost:8000/simple%20quiz.html

## 📁 Project Files Structure
```
online-quiz-system/
├── simple quiz.html          (Main frontend)
├── config.php                (Database config)
├── get_quizzes.php          (Fetch quizzes from DB)
├── database_schema.sql      (Database schema + sample data)
├── SETUP_GUIDE.md           (Detailed setup instructions)
└── SETUP_CHECKLIST.md       (This file)
```

## 🧪 Testing After Setup

1. **Test Database Connection**
   - In browser, visit: http://localhost:8000/config.php
   - Should show no errors

2. **Test Quiz Loading**
   - Go to: http://localhost:8000/simple%20quiz.html
   - Click "Quizzes" tab
   - Should display quizzes from database

3. **Check Database**
   - Run: `mysql -u root -p`
   - Run: `USE quiz_system;`
   - Run: `SELECT * FROM quizzes;`
   - Should show 4 sample quizzes

## 💡 Next Steps After Testing
Once the database is working, we can convert:
- [ ] Leaderboard to use database
- [ ] Quiz questions to use database
- [ ] User profile/stats to use database
- [ ] Login/authentication system
- [ ] Quiz submission and scoring

## ❓ Troubleshooting

**"Connection failed" error**
- Is MySQL running? Check Windows Services
- Is password correct in config.php?
- Is database name correct?

**"Port 3306 already in use"**
- MySQL might already be running (good!)
- Or another service is using that port
- Run: `netstat -ano | findstr :3306`

**PHP can't find config.php**
- Make sure files are in the same folder
- Path should be: `C:\Users\ricjo\OneDrive\Desktop\CPSC 332 Project\online-quiz-system\`

**No quizzes showing**
- Check database was created: `mysql -u root -p quiz_system`
- Check data was inserted: `SELECT * FROM quizzes;`
- Check config.php has correct password

## 👥 For Group Members
When your team members set up, they need to:
1. Clone the same repository
2. Install MySQL (if not already done)
3. Import database_schema.sql (creates database and sample data)
4. Update config.php with their MySQL password
5. Run: `php -S localhost:8000`

They can use the same database or create separate ones for testing.

