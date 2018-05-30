<?php
include "connettiDB.php";
$connection = connettiDB();
session_start();

$requester = $_SESSION['login_user'];
$title = $_REQUEST['name'];
$expirationDate = $_REQUEST['date'];
date_default_timezone_set("America/New_York");
$nowDate = date('Y-m-d');

$query = "INSERT INTO p4c.campaign(title, requester, opening_date, deadline_date, registration_deadline_date) VALUES ($1, $2, $3, $4, $5)";

$done = pg_query_params($connection, $query, array($title, $requester, $nowDate, $expirationDate, $expirationDate));

header("Location:/P4C/homepage.php");
