<?php

// Get Railway's DATABASE_URL environment variable
$databaseUrl = getenv('DB_CONNECTION') ?: $_ENV['DB_CONNECTION'] ?: null;

if ($databaseUrl) {
    // Parse Railway's DATABASE_URL format: mysql://user:password@host:port/database
    $parsedUrl = parse_url($databaseUrl);

    $host = $parsedUrl['host'] ?? 'localhost';
    $port = $parsedUrl['port'] ?? 3306;
    $user = $parsedUrl['user'] ?? 'root';
    $pass = $parsedUrl['pass'] ?? '';
    $dbname = ltrim($parsedUrl['path'] ?? '', '/');

    // Build PDO DSN
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    error_log("Using Railway DATABASE_URL - Host: {$host}, Port: {$port}, DB: {$dbname}, User: {$user}");
} else {
    // Fallback to individual environment variables
    $host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?: 'localhost';
    $port = getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?: '3306';
    $user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?: 'root';
    $pass = getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?: '';
    $dbname = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?: 'railway';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    error_log("Using individual env vars - Host: {$host}, Port: {$port}, DB: {$dbname}, User: {$user}");
}

// PDO options for Railway MySQL connection
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::ATTR_TIMEOUT            => 30
];

try {
    // Create PDO connection
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Test the connection
    $pdo->query("SELECT 1");

    error_log("Database connection successful to {$host}:{$port}/{$dbname}");

} catch (PDOException $e) {
    error_log("Database Connection Failed: " . $e->getMessage());
    error_log("DSN: {$dsn}, User: {$user}");

    // Don't expose sensitive connection details to the user
    die("Database Connection Failed: " . $e->getMessage());
}

?>
