<?php

    //Add Contacts into the Contacts table
   
    require_once 'db.php';
    $inData = getRequestInfo();

    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $email = $inData["email"];
    $phone = $inData["phone"];
    $userId = $inData["userId"];

    $stmt = $pdo->prepare("INSERT INTO contacts_tb (first_name, last_name, email, phone, user_id) VALUES (?, ?, ?, ?, ?) ");
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $userId);
    
    if($stmt->excute()) {
        
        returnWithInfo();
    
    } else {
    
        returnWithError("Add Contact Failed");

    }

    function getRequestInfo() {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson($obj) {
        header('Content-type: application/json');
        echo $obj;
    }

    function returnWithInfo()
    {

        $retValue = json_encode([
        "success" => true,
        "message" => "Operation completed successfully",
        "timestamp" => date('Y-m-d H:i:s'),
        ]);
        
        sendResultInfoAsJson($retValue);
    }

    function returnWithError($err) {
        $retValue = json_encode([
            "id" => 0,
            "firstName" => "",
            "lastName" => "",
            "error" => $err,
            "timestamp" => date('Y-m-d H:i:s'),
            "success" => false
        ]);
        sendResultInfoAsJson($retValue);
    }
?>