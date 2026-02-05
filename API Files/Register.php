<?php
    $inData = getRequestInfo();

    $firstName = $inData["firstname"];
    $lastName = $inData["lastName"];
    $username = $inData["login"];
    $password = $inData["password"];

    $conn = new mysqli();

    if($conn->connect_error)
    {
        returnWithError( $conn->connect_error );
    }
    else
    {
        //Add user to User table
        $stmt = $conn->prepare("INSERT INTO Users(firstname, lastname, login, password) VALUES(?,?,?,?)");
        $stmt->bind_param("ss", $firstName, $lastName, $username, $password);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        returnWithError("");
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
        $retValue = '{"error":"' .$err .'"}';
        sendResultInfoAsJson( $retValue );
    }
?>