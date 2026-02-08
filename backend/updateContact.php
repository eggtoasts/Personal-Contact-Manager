<?php

    //Update Contacts in the Contacts table

    require_once 'db.php';
    $inData = getRequestInfo();

    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $email = $inData["email"];
    $phone = $inData["phone"];
    $contactId = $inData["contactId"];

    $stmt = $pdo->prepare("UPDATE contacts_tb SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE contacts_id = ?");
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $contactId);

    if($stmt->excute()) {
        returnWithError("");
    } else {
        returnWithError("Contact Update Failed");
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

    /*UPDATE table_name
    SET column1 = value1, column2 = value2, ...
    WHERE condition;   */
?>