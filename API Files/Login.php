<?php

    $inData = getRequestInfo();

    $id = 0;
    $firstName = "";
    $lastName = "";

    $conn = new mysqli("localhost", "Admin", "March16", "smallproject_db"); 	

    if($conn->connect_error)
    {
        returnWithError($conn->connect_error);
    }
    else
    {
        //query User table for a match
        $stmt = $conn->prepare("SELECT ID, first_name, last_name FROM Users WHERE login=? AND password=?");
        $stmt->bind_param("ss", $inData["login"], $inData["password"]);
        $stmt->execute();
        $result = $stmt->get_result();

        //Returns user info if match is found from table
        if($row = $result->fetch_assoc() )
        {
            returnWithInfo($row["login"], $row["password"], $row["ID"]);
        } 
        else{
			returnWithError("No Records Found");
        }

        $stmt->close();
        $conn->close();
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

    //Return error as json
    function returnWithError ( $err )
    {
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
        sendResultInfoAsJson( $retValue );
    }

    //Return information as json 
    function returnWithInfo( $firstName, $lastName, $id )
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}

?>
