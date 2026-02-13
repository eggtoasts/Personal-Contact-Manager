<?php

    //access to the data base
    require_once 'db.php'; 
    $inData = getRequestInfo();

    $contactCount = 0;
    $contactResults = "";
    $userId = $inData["userId"];


    try{

        //prepare statement 
        //requests first, last name, phone and email columns from the database usign partials of the first and last name ONLY from the contacts of the user specified
        $stmt = $pdo->prepare("SELECT contacts_id, first_name, last_name, phone, email, created from contacts_tb WHERE (first_name like ? OR last_name like ?) AND user_id = ?");
		$ContactName = "%" . $inData["search"] . "%"; //partial match 
        $stmt->execute([$ContactName, $ContactName, $inData["userId"]]); //gets the ? values (String, String , integer)

        while($row = $stmt->fetch()) {

            if( $contactCount > 0 ){
                $contactResults .= ",";
            }

            $contactCount++;

            //testing new way of setting ou the resulting Json string
            $contactResults .= '{"id" : "' . $row["contacts_id"] . '",
                                "firstName" : "' . $row["first_name"] . '",
                                "lastName" : "' . $row["last_name"] . '",
                                "email" : "' . $row["email"] . '",
                                "phone" : "' . $row["phone"] . '",
                                "created" : "' . $row["created"] . '"
                                }';
        }

    
        //returns result
        if($contactCount === 0){
            returnWithError("No Contacts Found");
        } else {
            returnWithInfo( $contactResults );
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

    function returnWithError( $err )
	{
		$retValue = json_encode([
            "id"=>0,
            "firstName" => "",
            "lastName" => "",
            "error"=> $err,
            "timestamp"=> date('Y-m-d H:i:s'),
            "sucess"=> false,
        ]);
        
		sendResultInfoAsJson( $retValue );
	}

    function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}



?>
