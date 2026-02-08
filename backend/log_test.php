<?php
require_once 'middleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Generate test logs for Railway visibility
function generateTestLogs() {
    $timestamp = date('Y-m-d H:i:s T');

    // Test different log levels
    error_log("[LOG_TEST] $timestamp - INFO: Log test endpoint accessed");
    error_log("[LOG_TEST] $timestamp - DEBUG: Testing debug level logging");
    error_log("[LOG_TEST] $timestamp - WARN: Testing warning level logging");

    // Test PHP error logging
    trigger_error("Test notice for Railway log visibility", E_USER_NOTICE);

    // Test stdout logging
    echo "<!-- STDOUT LOG: $timestamp - Testing stdout output -->\n";

    // Test stderr logging via error_log
    error_log("STDERR LOG: $timestamp - Testing stderr output");

    // Test application-specific logging
    if (function_exists('logResponse')) {
        logResponse(200, "Log test completed successfully");
    }
}

try {
    generateTestLogs();

    $logInfo = [
        'status' => 'success',
        'message' => 'Log test completed - check Railway logs for output',
        'timestamp' => date('Y-m-d H:i:s T'),
        'tests_performed' => [
            'error_log() function',
            'PHP notice trigger',
            'stdout output',
            'stderr output',
            'middleware logging',
            'supervisor logging'
        ],
        'log_locations' => [
            'nginx_access' => '/dev/stdout (via symlink)',
            'nginx_error' => '/dev/stderr (via symlink)',
            'php_errors' => '/proc/self/fd/2 (stderr)',
            'supervisor' => '/dev/stdout and /dev/stderr',
            'application' => 'requests.log and error_log()'
        ],
        'railway_visibility' => [
            'All error_log() calls should appear in Railway logs',
            'Nginx access logs should show HTTP requests',
            'PHP-FPM logs should show process information',
            'Application logs should show API requests via middleware'
        ],
        'test_commands' => [
            'Check Railway deployment logs',
            'Monitor real-time logs in Railway dashboard',
            'Use railway logs command if using Railway CLI',
            'Check /logs endpoint for application-specific logs'
        ]
    ];

    // Log the response
    error_log("[LOG_TEST] Response: " . json_encode($logInfo));

    echo json_encode($logInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    error_log("[LOG_TEST] ERROR: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Log test failed',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s T')
    ]);
}
?>
