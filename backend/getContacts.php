<?php

    require_once 'db.php';
    $inData = getRequestInfo();

    $contactCount = 0;
    $contactResults = "";
    $userId = $inData["userId"];

    try{

        $stmt = $pdo->prepare("SELECT first_name, last_name, phone, email, created, contacts_id from contacts_tb WHERE user_id = ?");

        if($stmt->execute([$userId])) {
            while($row = $stmt->fetch()) {

                if( $contactCount > 0 ){
                    $contactResults .= ",";
                }

                $contactCount++;
                $contactResults .= '{"id" : "' . $row["contacts_id"] . '",
                                    "firstName" : "' . $row["first_name"] . '",
                                    "lastName" : "' . $row["last_name"] . '",
                                    "email" : "' . $row["email"] . '",
                                    "phone" : "' . $row["phone"] . '",
                                    "created" : "' . $row["created"] . '"
                                    }';
            }

            if($contactCount == 0) {
                returnWithError("No Contacts Found");
            } else {
                returnWithInfo( $contactResults );
            }
        }

        $stmt = null;

    } catch(PDOException $e){
        returnWithError($e->getMessage());
    }

    function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

    function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

     function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
    
    function returnWithError( $err )
	{
		 $retValue = json_encode([
            "id" => 0,
            "firstName" => "",
            "lastName" => "",
            "error" => $err,
            "timestamp" => date('Y-m-d H:i:s'),
            "success" => false,
        ]);       

        sendResultInfoAsJson( $retValue );
	}
?>
