<?php

    //Add Contacts into the Contacts table
   
    require_once 'dp.php';
    $inData = getRequestInfo();

    $firstName = $inData["firstname"];
    $lastName = $inData["lastName"];
    $email = $inData["email"];
    $phone = $inData["phone"];
    $created = $inData["created"];
    $userId = $inData["userId"];

    $stmt = $pdo->prepare("INSERT INTO contacts_tb (first_name, last_name, email, phone, user_id) VALUES (?, ?, ?, ?, ?)) ");

    if($stmt->is_execute([$firstName, $lastName, $email, $phone, $user_id])) {
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