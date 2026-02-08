<?php
header('Content-Type: application/json');

try {
    // Get database configuration from container environment variables
    // Railway injects these directly into the container
    $host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?: 'localhost';
    $db = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?: 'contact_manager';
    $user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?: 'root';
    $pass = getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?: '';
    $port = getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?: '3306';
    $charset = 'utf8mb4';

    // Check if we have a full connection string (Railway style)
    $dsn = getenv('DB_CONNECTION') ?: $_ENV['DB_CONNECTION'] ?: null;

    if ($dsn === null) {
        // Build DSN from individual components
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Test database connection
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Test query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();

    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection successful',
        'test_query' => $result,
        'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed',
        'error' => $e->getMessage(),
        'env_check' => [
            'DB_HOST' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'not set'),
            'DB_NAME' => getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'not set'),
            'DB_USER' => getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'not set'),
            'DB_PORT' => getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 'not set'),
            'DB_PASS' => (getenv('DB_PASS') || isset($_ENV['DB_PASS'])) ? 'set' : 'not set',
            'DB_CONNECTION' => getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'not set')
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'General error',
        'error' => $e->getMessage()
    ]);
}
?>
