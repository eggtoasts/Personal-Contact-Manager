<?php
    //Delete contact from contact table

    require_once 'dp.php';
    
    $inData = getRequestInfo();

    $stmt = $pdo->prepare("DELETE FROM contacts_tb WHERE contacts_id = ?")
    $stmt->bind_param("s", $inData["contactId"] )

    if($stmt->execute()) {
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