<?php
include "connettiDB.php";
$connection = connettiDB();
session_start();

$requester = $_SESSION['login_user'];
$title = $_REQUEST['title'];
$description = $_REQUEST['desc'];
$workers = $_REQUEST['workers'];
$tresh = $_REQUEST['magg'];

$query = "INSERT INTO p4c.task(titolo, description, n_worker, majority_threshold, requester) VALUES ($1, $2, $3, $4, $5)";

$done = pg_query_params($connection, $query, array($title, $description, $workers, $tresh, $requester));

header("Location:/P4C/homepage.php");
