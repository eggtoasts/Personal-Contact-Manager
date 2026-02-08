<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query parameters and leading/trailing slashes
$path = trim(parse_url($requestUri, PHP_URL_PATH), '/');

// If path is empty, show API info
if (empty($path)) {
    echo json_encode([
        'message' => 'Contact Manager API',
        'version' => '1.0',
        'status' => 'running',
        'endpoints' => [
            'POST /Login' => 'User authentication',
            'POST /Register' => 'User registration',
            'GET /getContacts' => 'Get all contacts',
            'POST /addContact' => 'Add new contact',
            'PUT /updateContact' => 'Update contact',
            'DELETE /deleteContact' => 'Delete contact',
            'GET /searchContacts' => 'Search contacts'
        ]
    ]);
    exit();
}

// Define available endpoints and their corresponding files
$routes = [
    'Login' => 'Login.php',
    'Register' => 'Register.php',
    'addContact' => 'addContact.php',
    'getContacts' => 'getContacts.php',
    'updateContact' => 'updateContact.php',
    'deleteContact' => 'deleteContact.php',
    'editContact' => 'editContact.php',
    'searchContacts' => 'searchContacts.php',
    'db' => 'db.php'
];

// Check if the requested endpoint exists
if (!isset($routes[$path])) {
    http_response_code(404);
    echo json_encode([
        'error' => 'Endpoint not found',
        'path' => $path,
        'available_endpoints' => array_keys($routes)
    ]);
    exit();
}

$filename = $routes[$path];
$filepath = __DIR__ . '/' . $filename;

// Check if the file exists
if (!file_exists($filepath)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'File not found',
        'file' => $filename,
        'path' => $path
    ]);
    exit();
}

// Include and execute the requested file
try {
    require_once $filepath;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'file' => $filename
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'PHP Error',
        'message' => $e->getMessage(),
        'file' => $filename
    ]);
}
?>
