<?php
// Database connection configuration
// This file provides a reusable database connection for all PHP files

/**
 * Get database connection
 * Returns a PDO connection object
 */
function getDbConnection() {
    try {
        $conn = new PDO(
            'mysql:host=localhost;dbname=quiz_system',
            'quiz_user',
            ''
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        // Return null if connection fails
        // The calling script should check for null and handle the error
        return null;
    }
}
?>
