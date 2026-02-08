<?php

    //Add Contacts into the Contacts table
   
    require_once 'db.php';
    $inData = getRequestInfo();

    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $email = $inData["email"];
    $phone = $inData["phone"];
    $userId = $inData["userId"];

    $stmt = $pdo->prepare("INSERT INTO contacts_tb (first_name, last_name, email, phone, user_id) VALUES (?, ?, ?, ?, ?)) ");
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $userId);
    
    if($stmt->excute()) {
        returnWithError("");
    } else {
        returnWithError("Add Contact Failed");
    }

    function getRequestInfo() {
        return json_decode(file_get_contents('php//input'), true);
    }

    function sendResultInfoAsJson($obj) {
        header('Content-type: application/json');
    }

    function returnWithError($err) {
        $retValue = json_encode(["error" => $err]);
        sendResultIndoAsJson($retValue);
    }
?>