<?php

    //access to the data base
    require_once 'db.php'; 
    $inData = getRequestInfo();

    $contactCount = 0;
    $contactResults = "";


    try{

        //prepare statement 
        //requests first, last name, phone and email columns from the database usign partials of the first and last name ONLY from the contacts of the user specified
        $stmt = $pdo->prepare("SELECT first_name, last_name, phone, email, created from contacts_tb WHERE (first_name like ? OR last_name like ?) AND user_id = ?");
		$ContactName = "%" . $inData["search"] . "%"; //partial match 
        $stmt->bind_param("ssi", $ContactName, $ContactName, $inData["userId"]); //gets the ? values (String, String , integer)
        $stmt->execute();

        $result = $stmt->get_result();  //gets result of request 

        while($row = $result->fetch_assoc()) {

            if( $contactCount > 0 ){
                $contactResults .= ",";
            }

            $contactCount++;
            // $contactResults .= '{"firstName" : "' . $row["firstName"] . '"},
            //                     {"lastName" : "' . $row["lastName"] . '"},
            //                     {"email" : "' . $row["email"] . '"},
            //                     {"phone" : "' . $row["phone"] . '"},
            //                     {"created" : "' . $row["created"] . '"}
            //                     ';

            //testing new way of setting ou the resulting Json string
            $contactResults .= '{"firstName":"' . $row["firstName"] . '",
                                 "lastName":"' . $row["lastName"] . '",
                                 "email":"' . $row["email"] . '",
                                 "phone":"' . $row["phone"] . '",
                                 "created":"' . $row["created"] . '"}';
        }

    
        //returns result
        if($contactCount === 0){
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
