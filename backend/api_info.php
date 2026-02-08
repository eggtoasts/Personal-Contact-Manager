<?php
require_once 'middleware.php';

header('Content-Type: application/json');

// Only allow GET requests for this endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed',
        'message' => 'This endpoint only accepts GET requests'
    ]);
    exit();
}

$apiInfo = [
    'message' => 'Contact Manager API',
    'version' => '1.0',
    'status' => 'running',
    'server_time' => date('Y-m-d H:i:s T'),
    'endpoints' => [
        [
            'path' => '/Login',
            'method' => 'POST',
            'description' => 'User authentication',
            'required_fields' => ['login', 'password']
        ],
        [
            'path' => '/Register',
            'method' => 'POST',
            'description' => 'User registration',
            'required_fields' => ['firstName', 'lastName', 'email', 'password']
        ],
        [
            'path' => '/getContacts',
            'method' => 'POST',
            'description' => 'Get all contacts for a user',
            'required_fields' => ['userId']
        ],
        [
            'path' => '/addContact',
            'method' => 'POST',
            'description' => 'Add new contact',
            'required_fields' => ['userId', 'firstName', 'lastName', 'phone', 'email']
        ],
        [
            'path' => '/updateContact',
            'method' => 'POST',
            'description' => 'Update existing contact',
            'required_fields' => ['contactId', 'firstName', 'lastName', 'phone', 'email']
        ],
        [
            'path' => '/deleteContact',
            'method' => 'POST',
            'description' => 'Delete contact',
            'required_fields' => ['contactId']
        ],
        [
            'path' => '/searchContacts',
            'method' => 'POST',
            'description' => 'Search contacts',
            'required_fields' => ['userId', 'search']
        ]
    ],
    'test_endpoints' => [
        [
            'path' => '/api_info.php',
            'method' => 'GET',
            'description' => 'This endpoint - API information'
        ],
        [
            'path' => '/db_test.php',
            'method' => 'GET',
            'description' => 'Database connection test'
        ],
        [
            'path' => '/logs',
            'method' => 'GET',
            'description' => 'View request logs (add ?lines=100 for more logs)'
        ],
        [
            'path' => '/logs?action=clear',
            'method' => 'GET',
            'description' => 'Clear request logs'
        ],
        [
            'path' => '/swagger',
            'method' => 'GET',
            'description' => 'Interactive API documentation (Swagger UI)'
        ],
        [
            'path' => '/docs',
            'method' => 'GET',
            'description' => 'API documentation (alias for /swagger)'
        ],
        [
            'path' => '/swagger.json',
            'method' => 'GET',
            'description' => 'OpenAPI specification in JSON format'
        ],
        [
            'path' => '/health',
            'method' => 'GET',
            'description' => 'Health check endpoint'
        ]
    ],
    'notes' => [
        'All API endpoints (except test endpoints) require POST requests with JSON data',
        'Content-Type header must be set to application/json',
        'CORS is enabled for all origins',
        'All endpoints return JSON responses',
        'Interactive API documentation available at /swagger or /docs',
        'OpenAPI specification available at /swagger.json'
    ]
];

logResponse(200, json_encode($apiInfo));
echo json_encode($apiInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
