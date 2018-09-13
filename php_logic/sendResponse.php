<?php
include "connettiDB.php";
$connection = connettiDB();
session_start();

$worker = $_SESSION['login_user'];
$response = $_REQUEST['response'];
$campaign = $_REQUEST['camp'];

$query = "SELECT * FROM p4c.campaign AS C WHERE (now()::date BETWEEN C.opening_date AND C.registration_deadline_date) AND C.title = $1";
$done = pg_query_params($connection, $query, array($campaign));

if (!$done) {
    echo "<script type='text/javascript'> alert('Registration date expired'); </script>";
    header("Location:/P4C/homepage.php");
} else {
    $query = "INSERT INTO p4c.made_response(worker, response) VALUES ($1, $2)";
    pg_query_params($connection, $query, array($worker, $response));
    header("Location:/P4C/homepage.php");
}
header("Location:/P4C/homepage.php");
