<?php
require_once 'middleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'view';
$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 50;

try {
    switch ($method) {
        case 'GET':
            if ($action === 'clear') {
                clearRequestLogs();
                echo json_encode([
                    'success' => true,
                    'message' => 'Logs cleared successfully'
                ]);
            } else {
                // Default: view logs
                $logs = getRecentLogs($lines);

                // Parse logs into structured format
                $logLines = array_filter(explode("\n", $logs));
                $parsedLogs = [];

                foreach ($logLines as $line) {
                    if (trim($line)) {
                        // Try to parse the log line
                        if (preg_match('/^\[(.*?)\] (.*?) (.*?) \|/', $line, $matches)) {
                            $parsedLogs[] = [
                                'timestamp' => $matches[1],
                                'method' => $matches[2],
                                'endpoint' => $matches[3],
                                'full_line' => $line
                            ];
                        } else {
                            // If parsing fails, just include the raw line
                            $parsedLogs[] = [
                                'timestamp' => date('Y-m-d H:i:s'),
                                'method' => 'UNKNOWN',
                                'endpoint' => 'UNKNOWN',
                                'full_line' => $line
                            ];
                        }
                    }
                }

                echo json_encode([
                    'success' => true,
                    'total_entries' => count($parsedLogs),
                    'showing_lines' => $lines,
                    'logs' => array_reverse($parsedLogs), // Show most recent first
                    'raw_logs' => $logs
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
            break;

        case 'DELETE':
            clearRequestLogs();
            echo json_encode([
                'success' => true,
                'message' => 'Logs cleared successfully'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'error' => 'Method not allowed',
                'allowed_methods' => ['GET', 'DELETE']
            ]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

// Log this request too
logResponse(http_response_code(), 'Logs endpoint accessed');
?>
