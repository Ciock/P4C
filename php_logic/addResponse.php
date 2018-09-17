<?php
include "connettiDB.php";
$connection = connettiDB();
session_start();

$task = $_REQUEST['task'];
$task = urldecode($task);
$query_id_task = "SELECT id FROM p4c.task WHERE titolo = $1";
$fake_id = pg_query_params($connection, $query_id_task, array($task));
$id = pg_fetch_row($fake_id);
$response = $_REQUEST['response'];

if ($_REQUEST['button']=="Add") {
    $query = "INSERT INTO p4c.response(name,task) VALUES ($1, $2)";
    $done = pg_query_params($connection, $query, array($response, $id[0]));
    $task = urlencode($task);
    $header = "Location:/P4C/add_Response.php?task=".$task."&id=".$id[0]."&response=".$response;
    header($header);
}else{
    header("Location:/P4C/homepage.php");
}
