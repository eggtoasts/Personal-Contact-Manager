<?php
    require_once 'db.php';
    $inData = getRequestInfo();
try {
       
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, password FROM user_tb WHERE email = ?");
        $stmt->execute([ $inData["login"] ]);
        $user = $stmt->fetch();

        if ($user && password_verify($inData["password"], $user["password"])) {
            returnWithInfo($user["first_name"], $user["last_name"], $user["user_id"]);
        } 
        else {
            returnWithError("Invalid Email or Password");
        }

    } catch (PDOException $e) {
        returnWithError($e->getMessage());
    }

   

    function getRequestInfo() {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson($obj) {
        header('Content-type: application/json');
        echo $obj;    
    }

    function returnWithError($err) {
        $retValue = json_encode([
            "id" => 0,
            "firstName" => "",
            "lastName" => "",
            "error" => $err
        ]);
        sendResultInfoAsJson($retValue);
    }

    function returnWithInfo($firstName, $lastName, $id) {
        $retValue = json_encode([
            "id" => $id,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "error" => ""
        ]);
        sendResultInfoAsJson($retValue);
    }
?>