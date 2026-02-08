<?php
require_once 'middleware.php';
require_once 'db.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    logResponse(200, 'OPTIONS preflight request handled');
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $error = [
        "id" => 0,
        "firstName" => "",
        "lastName" => "",
        "error" => "Method not allowed. Only POST requests are accepted.",
        "debug" => [
            "received_method" => $_SERVER['REQUEST_METHOD'],
            "allowed_methods" => ["POST"]
        ]
    ];
    logResponse(405, json_encode($error));
    echo json_encode($error);
    exit();
}

try {
    error_log("[LOGIN] Starting login attempt");

    // Get and validate request data
    $inData = getRequestInfo();

    error_log("[LOGIN] Request data received: " . ($inData ? "valid JSON" : "no data"));

    // Validate that we have the required fields
    if (!$inData) {
        error_log("[LOGIN] ERROR: No input data received");
        returnWithError("No data received. Please send JSON data in request body.", 400);
        exit();
    }

    if (!isset($inData["login"]) || empty(trim($inData["login"]))) {
        error_log("[LOGIN] ERROR: Missing login/email field");
        returnWithError("Email/login is required.", 400);
        exit();
    }

    if (!isset($inData["password"]) || empty($inData["password"])) {
        error_log("[LOGIN] ERROR: Missing password field");
        returnWithError("Password is required.", 400);
        exit();
    }

    $email = trim($inData["login"]);
    $password = $inData["password"];

    error_log("[LOGIN] Login attempt for email: " . $email);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("[LOGIN] ERROR: Invalid email format for: " . $email);
        returnWithError("Invalid email format.", 400);
        exit();
    }

    // Check if database connection exists
    if (!isset($pdo) || !$pdo) {
        error_log("[LOGIN] ERROR: No database connection available");
        returnWithError("Database connection failed. Please try again later.", 500);
        exit();
    }

    error_log("[LOGIN] Database connection verified");

    // Prepare and execute database query
    error_log("[LOGIN] Preparing database query for user lookup");
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, password FROM user_tb WHERE email = ?");

    if (!$stmt) {
        error_log("[LOGIN] ERROR: Database query preparation failed");
        returnWithError("Database query preparation failed.", 500);
        exit();
    }

    error_log("[LOGIN] Executing query for email: " . $email);
    $executed = $stmt->execute([$email]);

    if (!$executed) {
        error_log("[LOGIN] ERROR: Database query execution failed for email: " . $email);
        returnWithError("Database query execution failed.", 500);
        exit();
    }

    $user = $stmt->fetch();

    if (!$user) {
        // User not found - don't reveal this information for security
        error_log("[LOGIN] ERROR: User not found for email: " . $email);
        returnWithError("Invalid email or password.", 401);
        exit();
    }

    error_log("[LOGIN] User found in database: " . $user['first_name'] . " " . $user['last_name']);

    // Verify password
    error_log("[LOGIN] Verifying password for user: " . $email);
    if (!password_verify($password, $user["password"])) {
        error_log("[LOGIN] ERROR: Password verification failed for email: " . $email);
        returnWithError("Invalid email or password.", 401);
        exit();
    }

    error_log("[LOGIN] Password verification successful for: " . $email);

    // Validate user data
    if (!isset($user["user_id"]) || !isset($user["first_name"]) || !isset($user["last_name"])) {
        error_log("[LOGIN] ERROR: Incomplete user data for email: " . $email);
        returnWithError("User data is incomplete. Please contact support.", 500);
        exit();
    }

    // Success - return user information
    error_log("[LOGIN] SUCCESS: Login successful for user ID: " . $user["user_id"] . " (" . $user["first_name"] . " " . $user["last_name"] . ")");
    returnWithInfo($user["first_name"], $user["last_name"], $user["user_id"]);

} catch (PDOException $e) {
    // Database specific errors
    error_log("[LOGIN] PDO ERROR: " . $e->getMessage());
    error_log("[LOGIN] PDO ERROR Stack trace: " . $e->getTraceAsString());
    returnWithError("Database error occurred. Please try again later.", 500);
} catch (Exception $e) {
    // General errors
    error_log("[LOGIN] EXCEPTION: " . $e->getMessage());
    error_log("[LOGIN] EXCEPTION Stack trace: " . $e->getTraceAsString());
    returnWithError("An unexpected error occurred. Please try again later.", 500);
} catch (Error $e) {
    // PHP fatal errors
    error_log("[LOGIN] FATAL ERROR: " . $e->getMessage());
    error_log("[LOGIN] FATAL ERROR Stack trace: " . $e->getTraceAsString());
    returnWithError("A system error occurred. Please try again later.", 500);
}

function getRequestInfo() {
    try {
        $input = file_get_contents('php://input');
        error_log("[LOGIN] Raw input received: " . strlen($input) . " bytes");

        if (empty($input)) {
            error_log("[LOGIN] WARNING: Empty input received");
            return null;
        }

        $decoded = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[LOGIN] JSON ERROR: " . json_last_error_msg());
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }

        error_log("[LOGIN] JSON decoded successfully with " . count($decoded) . " fields");
        return $decoded;

    } catch (Exception $e) {
        error_log("[LOGIN] JSON decode error: " . $e->getMessage());
        return null;
    }
}

function sendResultInfoAsJson($obj) {
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err, $httpCode = 400) {
    error_log("[LOGIN] Returning error response: HTTP $httpCode - $err");
    http_response_code($httpCode);

    $retValue = json_encode([
        "id" => 0,
        "firstName" => "",
        "lastName" => "",
        "error" => $err,
        "timestamp" => date('Y-m-d H:i:s'),
        "success" => false
    ]);

    logResponse($httpCode, $retValue);
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $id) {
    error_log("[LOGIN] Returning success response for user ID: $id");
    http_response_code(200);

    $retValue = json_encode([
        "id" => (int)$id,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "error" => "",
        "timestamp" => date('Y-m-d H:i:s'),
        "success" => true
    ]);

    logResponse(200, $retValue);
    sendResultInfoAsJson($retValue);
}
?>
