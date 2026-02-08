<?php
require_once 'middleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Only GET requests are accepted.']);
    exit();
}

// Get the current server URL for dynamic server configuration - force HTTPS for Railway
$protocol = 'https'; // Railway always uses HTTPS
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$baseUrl = $protocol . '://' . $host;

// Read and serve the swagger.json file with dynamic server URL
$swaggerFile = __DIR__ . '/swagger.json';

if (!file_exists($swaggerFile)) {
    http_response_code(404);
    echo json_encode([
        'error' => 'Swagger specification file not found',
        'message' => 'The swagger.json file is missing from the server'
    ]);
    exit();
}

try {
    $swaggerContent = file_get_contents($swaggerFile);
    $swaggerData = json_decode($swaggerContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON in swagger.json: ' . json_last_error_msg());
    }

    // Update server URLs dynamically
    $swaggerData['servers'] = [
        [
            'url' => $baseUrl,
            'description' => 'Current server (' . ($protocol === 'https' ? 'Production' : 'Development') . ')'
        ],
        [
            'url' => 'https://personal-contact-manager-production.up.railway.app',
            'description' => 'Railway Production server'
        ],
        [
            'url' => 'http://localhost:8000',
            'description' => 'Local development server'
        ]
    ];

    // Add generation timestamp
    $swaggerData['info']['x-generated-at'] = date('c');
    $swaggerData['info']['x-server-url'] = $baseUrl;

    // Log the request
    logResponse(200, 'Swagger JSON specification served');

    // Return the updated swagger specification
    echo json_encode($swaggerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to process swagger specification',
        'message' => $e->getMessage()
    ]);
    logResponse(500, 'Error serving swagger JSON: ' . $e->getMessage());
}
?>
