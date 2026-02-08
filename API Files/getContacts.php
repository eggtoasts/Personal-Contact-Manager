<?php

    require_once 'dp.php';
    $inData = getRequestInfo();

    $contactCount = 0;
    $contactResults = "";

    try{

        $stmt = $pdo->prepare("SELECT first_name, last_name, phone, email, created from contacts_tb WHERE user_id = ?");
        $stmt->bind_param("s", $inData["userId"]);
        $stmt->execute();

        $result = $stmt->get_result();

        while($row = $result->fetch_assoc()) {

            if( $contactCount > 0 ){
                $contactResults .= ",";
            }

            $contactCount++;
            $contactResults .= '{"firstName" : "' . $row["firstName"] . '"},
                                {"lastName" : "' . $row["lastName"] . '"}, 
                                {"email" : "' . $row["email"] . '"}, 
                                {"phone" : "' . $row["phone"] . '"}, 
                                {"created" : "' . $row["created"] . '"}
                                ';
        }

        if($contactCount == 0){
            returnWithError("No Contacts Found");
        } else {
            returnWithInfo( $contactResults );
        }

        $stmt->close();

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

    function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

    function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}



?>