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
    // Get and validate request data
    $inData = getRequestInfo();

    // Validate that we have the required fields
    if (!$inData) {
        returnWithError("No data received. Please send JSON data in request body.", 400);
        exit();
    }

    if (!isset($inData["login"]) || empty(trim($inData["login"]))) {
        returnWithError("Email/login is required.", 400);
        exit();
    }

    if (!isset($inData["password"]) || empty($inData["password"])) {
        returnWithError("Password is required.", 400);
        exit();
    }

    $email = trim($inData["login"]);
    $password = $inData["password"];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        returnWithError("Invalid email format.", 400);
        exit();
    }

    // Check if database connection exists
    if (!isset($pdo) || !$pdo) {
        returnWithError("Database connection failed. Please try again later.", 500);
        exit();
    }

    // Prepare and execute database query
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, password FROM user_tb WHERE email = ?");

    if (!$stmt) {
        returnWithError("Database query preparation failed.", 500);
        exit();
    }

    $executed = $stmt->execute([$email]);

    if (!$executed) {
        returnWithError("Database query execution failed.", 500);
        exit();
    }

    $user = $stmt->fetch();

    if (!$user) {
        // User not found - don't reveal this information for security
        returnWithError("Invalid email or password.", 401);
        exit();
    }

    // Verify password
    if (!password_verify($password, $user["password"])) {
        returnWithError("Invalid email or password.", 401);
        exit();
    }

    // Validate user data
    if (!isset($user["user_id"]) || !isset($user["first_name"]) || !isset($user["last_name"])) {
        returnWithError("User data is incomplete. Please contact support.", 500);
        exit();
    }

    // Success - return user information
    returnWithInfo($user["first_name"], $user["last_name"], $user["user_id"]);

} catch (PDOException $e) {
    // Database specific errors
    error_log("Login PDO Error: " . $e->getMessage());
    returnWithError("Database error occurred. Please try again later.", 500);
} catch (Exception $e) {
    // General errors
    error_log("Login General Error: " . $e->getMessage());
    returnWithError("An unexpected error occurred. Please try again later.", 500);
} catch (Error $e) {
    // PHP fatal errors
    error_log("Login Fatal Error: " . $e->getMessage());
    returnWithError("A system error occurred. Please try again later.", 500);
}

function getRequestInfo() {
    try {
        $input = file_get_contents('php://input');

        if (empty($input)) {
            return null;
        }

        $decoded = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }

        return $decoded;

    } catch (Exception $e) {
        error_log("JSON decode error: " . $e->getMessage());
        return null;
    }
}

function sendResultInfoAsJson($obj) {
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err, $httpCode = 400) {
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
