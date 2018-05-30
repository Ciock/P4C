<?php
include "connettiDB.php";
$connection = connettiDB();

$requester = $_SESSION['login_user'];
$title = $_REQUEST['name'];
$expirationDate = $_REQUEST['date'];
date_default_timezone_set("Europe/Rome");
$nowDate = "Europe/Rome:".date('Y-m-d');

pg_query_params($connection,
    "INSERT INTO p4c.campaign(title, requester, opening_date, deadline_date, registration_deadline_date) VALUES ($1, $2, $3, $4, $5)",
    array($title, $requester, $nowDate, $expirationDate, $expirationDate));
header("Location:/P4C/homepage.php");
