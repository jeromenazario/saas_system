<?php
$host = 'localhost';
$dbname = 'saas_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:20px;color:red;'><h2>Database Connection Failed</h2><p>" . $e->getMessage() . "</p><p>Make sure XAMPP is running and you have imported <strong>database.sql</strong> in phpMyAdmin.</p></div>");
}
?>
