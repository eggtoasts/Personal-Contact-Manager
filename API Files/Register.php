<?php

    require_once 'db.php';
    $inData = getRequestInfo();

    $firstName = $inData["firstname"];
    $lastName = $inData["lastName"];
    $username = $inData["login"];
    $password  = password_hash($inData["password"], PASSWORD_DEFAULT);

   try {
   
        $stmt = $pdo->prepare("INSERT INTO user_tb (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$firstName, $lastName, $username, $password])) {
            returnWithError(""); 
        } else {
            returnWithError("Registration Failed");
        }
    } catch (PDOException $e) {
     
        if ($e->getCode() == 23000) {
            returnWithError("That email is already registered.");
        } else {
            returnWithError($e->getMessage());
        }
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