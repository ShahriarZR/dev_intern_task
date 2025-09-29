<?php
function openCon()
{
    $dbhost = "Host_Name";
    $dbusername = "Database_Username";
    $password = "Database_Password";
    $dbname = "Database_Name";
    $conn = new mysqli($dbhost, $dbusername, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

?>
