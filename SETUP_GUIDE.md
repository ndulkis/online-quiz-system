# Quiz System - MySQL Setup Guide for School Project

## Requirements
- HTML & CSS (Frontend) ✓
- PHP (Backend) ✓ (Already installed - PHP 8.4.14)
- MySQL (Database) ✗ (Need to install)

## Option 1: Install MySQL Community Server (Recommended for School Projects)

### Step 1: Download MySQL
1. Go to: https://dev.mysql.com/downloads/mysql/
2. Select your OS (Windows)
3. Download MySQL 8.0 or 5.7 (either works fine)

### Step 2: Run Installer
1. Run the downloaded .msi file
2. Choose "Developer Default" installation type
3. Accept defaults until MySQL Server Configuration
4. Configure MySQL Server:
   - Port: 3306 (default)
   - MySQL Server type: Development Machine
   - Authentication method: Use Strong Password Encryption (recommended)
5. Set root password (remember this!)
6. Windows Service Name: MySQL80 (or MySQL57)
7. Complete installation

### Step 3: Verify Installation
After installation, open PowerShell and run:
```
mysql --version
mysql -u root -p
```

---

## Option 2: Use MySQL via Docker (If installed)
```
docker run --name quiz-mysql -e MYSQL_ROOT_PASSWORD=your_password -p 3306:3306 -d mysql:8.0
```

---

## Option 3: Use MariaDB (Open-source MySQL alternative)
Download from: https://mariadb.org/download/

MariaDB is fully compatible and often easier to install.

---

## After Installation: Create Database

1. Connect to MySQL:
```
mysql -u root -p
```

2. Run the database schema file from the project:
```
mysql -u root -p < database_schema.sql
```

Or manually copy and paste the SQL from `database_schema.sql` into MySQL client.

---

## Update PHP Config File

Once MySQL is running, update `config.php` with your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');  // The password you set during install
define('DB_NAME', 'quiz_system');
```

---

## Run the Application

1. Open terminal in the project folder
2. Run PHP's built-in server:
```
php -S localhost:8000
```

3. Open browser: http://localhost:8000/simple%20quiz.html

---

## Troubleshooting

**Error: "Connection failed"**
- Check MySQL service is running
- Verify credentials in config.php
- Check database name is correct

**Error: "Port 3306 already in use"**
- Use a different port in config.php (e.g., 3307)
- Or find what's using 3306: `netstat -ano | findstr :3306`

**PHP can't connect to MySQL**
- Check php.ini has mysqli extension enabled
- Run: `php -m | findstr mysqli`

