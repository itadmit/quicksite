<?php
// קובץ חיבור למסד נתונים
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'quicksite_db');
define('DB_PORT', '8889'); // MAMP default port

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // use real prepared statements
    
    // הגדרת ברירת מחדל של UTF-8 לתוצאות שאילתות
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET collation_connection = 'utf8mb4_unicode_ci'");
} catch (PDOException $e) {
    die("חיבור למסד הנתונים נכשל: " . $e->getMessage());
}