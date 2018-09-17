<?php
include "connettiDB.php";
$connection = connettiDB();
session_start();

$task = $_REQUEST['id'];
$response = $_REQUEST['response'];

if ($_REQUEST['button']=="Add") {
    $query = "INSERT INTO p4c.response(name,task) VALUES ($1, $2)";
    $done = pg_query_params($connection, $query, array($response, $task));
    $header = "Location:/P4C/addResponse.php?&id=".$task;
    header($header);
}else{
    header("Location:/P4C/homepage.php");
}
