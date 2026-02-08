<?php
/**
 * Request Logging Middleware
 * Logs all incoming requests with details for debugging
 */

class RequestLogger {
    private $logFile;
    private $startTime;

    public function __construct($logFile = 'requests.log') {
        $this->logFile = __DIR__ . '/' . $logFile;
        $this->startTime = microtime(true);
        $this->ensureLogFileExists();
    }

    private function ensureLogFileExists() {
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
            chmod($this->logFile, 0666);
        }
    }

    public function logRequest() {
        $timestamp = date('Y-m-d H:i:s');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip = $this->getClientIP();
        $headers = $this->getHeaders();
        $contentType = $_SERVER['CONTENT_TYPE'] ?? 'Not set';

        // Get request body for POST requests
        $body = '';
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $body = file_get_contents('php://input');
            // Limit body size for logging
            if (strlen($body) > 1000) {
                $body = substr($body, 0, 1000) . '... [truncated]';
            }
        }

        // Get query parameters
        $queryParams = $_GET ? json_encode($_GET) : 'None';

        $logEntry = sprintf(
            "[%s] %s %s | IP: %s | User-Agent: %s | Content-Type: %s | Query: %s | Body: %s | Headers: %s\n",
            $timestamp,
            $method,
            $uri,
            $ip,
            $userAgent,
            $contentType,
            $queryParams,
            $body ?: 'Empty',
            json_encode($headers)
        );

        // Write to log file
        error_log($logEntry, 3, $this->logFile);

        // Also log to error log for immediate visibility
        error_log("API Request: $method $uri from $ip", 0);

        return $this;
    }

    public function logResponse($statusCode = null, $responseData = null) {
        $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);
        $timestamp = date('Y-m-d H:i:s');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if ($responseData && strlen($responseData) > 500) {
            $responseData = substr($responseData, 0, 500) . '... [truncated]';
        }

        $logEntry = sprintf(
            "[%s] RESPONSE %s | Time: %sms | Data: %s\n",
            $timestamp,
            $uri,
            $executionTime,
            $responseData ?: 'No response data'
        );

        if ($statusCode) {
            $logEntry = sprintf(
                "[%s] RESPONSE %s | Status: %s | Time: %sms | Data: %s\n",
                $timestamp,
                $uri,
                $statusCode,
                $executionTime,
                $responseData ?: 'No response data'
            );
        }

        error_log($logEntry, 3, $this->logFile);
    }

    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return 'Unknown';
    }

    private function getHeaders() {
        $headers = [];

        // Get all headers
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for servers that don't have getallheaders
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace('HTTP_', '', $key);
                    $header = str_replace('_', '-', $header);
                    $headers[$header] = $value;
                }
            }
        }

        // Filter out sensitive headers
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];
        foreach ($sensitiveHeaders as $sensitive) {
            foreach ($headers as $key => $value) {
                if (strtolower($key) === $sensitive) {
                    $headers[$key] = '[REDACTED]';
                }
            }
        }

        return $headers;
    }

    public function getLogContents($lines = 50) {
        if (!file_exists($this->logFile)) {
            return "No log file found.";
        }

        $content = file_get_contents($this->logFile);
        $logLines = explode("\n", $content);
        $recentLines = array_slice($logLines, -$lines);

        return implode("\n", $recentLines);
    }

    public function clearLog() {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
    }
}

// Auto-start logging for any request
function startRequestLogging() {
    global $requestLogger;
    $requestLogger = new RequestLogger();
    $requestLogger->logRequest();

    // Register shutdown function to log response
    register_shutdown_function(function() {
        global $requestLogger;
        if ($requestLogger) {
            $requestLogger->logResponse();
        }
    });
}

// Function to manually log responses with specific data
function logResponse($statusCode = null, $responseData = null) {
    global $requestLogger;
    if ($requestLogger) {
        $requestLogger->logResponse($statusCode, $responseData);
    }
}

// Function to get recent logs
function getRecentLogs($lines = 50) {
    $logger = new RequestLogger();
    return $logger->getLogContents($lines);
}

// Function to clear logs
function clearRequestLogs() {
    $logger = new RequestLogger();
    $logger->clearLog();
}

// Auto-initialize if this file is included
if (!defined('MIDDLEWARE_INITIALIZED')) {
    define('MIDDLEWARE_INITIALIZED', true);
    startRequestLogging();
}
?>
