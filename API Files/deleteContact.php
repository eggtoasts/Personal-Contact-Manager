<?php

    //Delete contact from contact table

    require_once 'dp.php';
    $inData = getRequestInfo();

    $userId = $inData["userId"];
    $email = $inData["email"];

    $stmt = $pdo->prepare("DELETE FROM contacts_tb WHERE user_id = ? AND email = ?")

    if($stmt->execute([$userId, $email])) {
        returnWithError("");
    } else {
        returnWithError("Delete Contact Failed");
    }

    function getRequestInfo() {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson($obj) {
        header('Content-type: application/json');
        echo $obj;
    }

    function returnWithError($err) {
        $retValue = json_encode(["error" => $err]);
        sendResultInfoAsJson($retValue);
    }
?>