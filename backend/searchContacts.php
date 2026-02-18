<?php

    //access to the data base
    require_once 'db.php'; 
    $inData = getRequestInfo();

    $contactCount = 0;
    $contactResults = "";
    $userId = $inData["userId"];
    $name = $inData["search"];

    try{

        //prepare statement 
        //requests first, last name, phone and email columns from the database using partials of the first and last name ONLY from the contacts of the user specified
        $partialStmt = $pdo->prepare("SELECT contacts_id, first_name, last_name, phone, email, created from contacts_tb WHERE (first_name like ? OR last_name like ?) AND user_id = ?");
        $fullStmt =  $pdo->prepare("SELECT contacts_id, first_name, last_name, phone, email, created from contacts_tb WHERE first_name = ? AND last_name = ? AND user_id = ?");

        //Remove extra whitespace from string
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        //Break string into first and last name
        $searchName = explode(' ', $name, 2);

        //Partial Search
        if(count($searchName) == 1) {

            $ContactName = "%" . $searchName[0] . "%"; //partial match 
            
            if($partialStmt->execute([$ContactName, $ContactName, $userId])) {
                countContacts($partialStmt);
            } else {
                returnWithError("Partial Search Error");
            }

        //Full name search
        } else {

            if($fullStmt->execute([$searchName[0], $searchName[1], $userId])) {
                countContacts($fullStmt);
            } else {
                returnWithError("Full Name Search Error");
            }
        }

        $partialStmt = null;
        $fullStmt = null

    } catch(PDOException $e){
        returnWithError($e->getMessage());
    }

    function countResults($stmt) {

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
