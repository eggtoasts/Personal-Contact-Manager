<?php

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Load the file


loadEnv(__DIR__ . '/.env');
$dsn = $_ENV['DB_CONNECTION'] ?? null;
if ($dsn == null) {  
    $host = $_ENV['DB_HOST'];
    $db   = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";;
    return;
}



$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
    PDO::ATTR_EMULATE_PREPARES   => false,                
];

try {

     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    
     die("Database Connection Failed: " . $e->getMessage());
}
?>