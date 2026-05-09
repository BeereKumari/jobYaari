<?php
define('DB_HOST', 'sql113.infinityfree.com');
define('DB_NAME', 'f0_41870764_jobyaari_blogs');
define('DB_USER', 'if0_41870764');
define('DB_PASS', 'sIFjdop1Lqy');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed.");
}
