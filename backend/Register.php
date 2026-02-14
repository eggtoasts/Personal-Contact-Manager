<?php

    require_once 'db.php';
    $inData = getRequestInfo();

    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $username = $inData["login"];
    $password  = password_hash($inData["password"], PASSWORD_DEFAULT);

   try {
   
        $stmt = $pdo->prepare("INSERT INTO user_tb (first_name, last_name, email, password) VALUES (?, ?, ?, ?)"); 

        if ($stmt->execute([$firstName, $lastName, $username, $password])) {
            
            // Get user_id from new user
            $sql = "SELECT user_id, first_name, last_name FROM user_tb WHERE email = ? AND password = ?";

            if($sql->execute([$username, $password])) {
             
                 $row = $sql->fetch();

                 returnWithInfo($row["user_id"]);
            }else {
                returnWithError("From Registration: Cannot Login");
            }
        
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

    function returnWithInfo($id)
	{
		$retValue = json_encode([
            "id" => $id,
            "firstName" => $firstName,
            "lastName" => $lastName,
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