<?php


// Get database configuration from container environment variables
// Railway injects these directly into the container
$host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?: 'localhost';
$db = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?: 'contact_manager';
$user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?: 'root';
$pass = getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?: '';
$port = getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?: '3306';
$charset = 'utf8mb4';

// Railway provides individual components, so build DSN from them
$dsn = getenv('DB_CONNECTION') ?: $_ENV['DB_CONNECTION'] ?: '';

if (empty($dsn)) {
    // Build DSN from individual components (Railway style)
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    error_log("Built DSN from components: host=$host, port=$port, db=$db, user=$user");
} else {
    error_log("Using provided DSN connection string");
}
error_log("Database DSN: mysql:host=$host;port=$port;dbname=$db (user=$user)");

// PDO options for better error handling and security
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    // Create PDO connection
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Log successful connection (for Railway visibility)
    error_log("Database connection successful: $host:$port/$db");

} catch (\PDOException $e) {
    // Log detailed error for Railway debugging
    error_log("Database Connection Failed: " . $e->getMessage());
    error_log("Connection details: host=$host, port=$port, db=$db, user=$user");

    // Don't expose sensitive details in the die message
    die("Database Connection Failed: Unable to connect to database server");
}

// Optional: Test connection with a simple query
try {
    $pdo->query("SELECT 1");
} catch (\PDOException $e) {
    error_log("Database connection test failed: " . $e->getMessage());
    die("Database Connection Failed: Connection established but database is not accessible");
}
?>
