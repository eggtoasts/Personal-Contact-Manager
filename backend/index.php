<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the request URI and remove query parameters
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remove leading slash and .php extension if present
$path = ltrim($path, '/');
$path = str_replace('.php', '', $path);

// Route the request to the appropriate file
switch ($path) {
    case 'db':
        require_once 'db.php';
        break;

    case 'Login':
        require_once 'Login.php';
        break;

    case 'Register':
        require_once 'Register.php';
        break;

    case 'addContact':
        require_once 'addContact.php';
        break;

    case 'getContacts':
        require_once 'getContacts.php';
        break;

    case 'updateContact':
        require_once 'updateContact.php';
        break;

    case 'deleteContact':
        require_once 'deleteContact.php';
        break;

    case 'editContact':
        require_once 'editContact.php';
        break;

    case 'searchContacts':
        require_once 'searchContacts.php';
        break;

    case '':
    case 'index':
        // API documentation or health check
        echo json_encode([
            'message' => 'Contact Manager API',
            'version' => '1.0',
            'endpoints' => [
                '/Login',
                '/Register',
                '/addContact',
                '/getContacts',
                '/updateContact',
                '/deleteContact',
                '/editContact',
                '/searchContacts'
            ]
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>
