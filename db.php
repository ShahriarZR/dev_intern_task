<?php
function openCon()
{
    $dbhost = "127.0.0.1";
    $dbusername = "root";
    $password = "root";
    $dbname = "task_tracker";
    $conn = new mysqli($dbhost, $dbusername, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

?>
