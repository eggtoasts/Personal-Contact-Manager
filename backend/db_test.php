<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

try {
    // Load environment variables
    loadEnv(__DIR__ . '/.env');

    // Get database configuration
    $dsn = $_ENV['DB_CONNECTION'] ?? null;

    if ($dsn == null) {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $db = $_ENV['DB_NAME'] ?? 'contact_manager';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    } else {
        // Use the provided DSN
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
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
            'DB_HOST' => $_ENV['DB_HOST'] ?? 'not set',
            'DB_NAME' => $_ENV['DB_NAME'] ?? 'not set',
            'DB_USER' => $_ENV['DB_USER'] ?? 'not set',
            'DB_PASS' => isset($_ENV['DB_PASS']) ? 'set' : 'not set',
            'DB_CONNECTION' => $_ENV['DB_CONNECTION'] ?? 'not set'
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
