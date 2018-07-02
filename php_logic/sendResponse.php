<?php
include "connettiDB.php";
$connection = connettiDB();
session_start();

$worker = $_SESSION['login_user'];
$response = $_REQUEST['response'];

$query = "INSERT INTO p4c.made_response(worker, response) VALUES ($1, $2)";

$done = pg_query_params($connection, $query, array($worker, $response));

header("Location:/P4C/homepage.php");
