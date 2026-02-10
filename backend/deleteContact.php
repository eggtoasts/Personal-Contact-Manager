<?php
    //Delete contact from contact table

    require_once 'db.php';
    
    $inData = getRequestInfo();
    $contactId = $inData["contactId"];

    $stmt = $pdo->prepare("DELETE FROM contacts_tb WHERE contacts_id = ?")

    if($stmt->execute([$contactId])) {
       
        returnWithInfo();

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

    function returnWithInfo() {
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
            "success" => false,
        ]);       
        
        sendResultInfoAsJson($retValue);
    }
?>