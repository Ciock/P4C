<?php
include "connettiDB.php";
$connection = connettiDB();
session_start();

$requester = $_SESSION['login_user'];
$title = $_REQUEST['title'];
$description = $_REQUEST['desc'];
$workers = $_REQUEST['workers'];
$tresh = $_REQUEST['magg'];
$campaign = $_REQUEST['campaign'];
$keywords = $_REQUEST['keywords'];


$tresh = doubleval($tresh);
$title = urldecode($title);
$query = "INSERT INTO p4c.task(titolo, description, n_worker, majority_threshold, requester, campaign) VALUES ($1, $2, $3, $4, $5, $6)";
$done = pg_query_params($connection, $query, array($title, $description, $workers, $tresh, $requester, $campaign));

$query = "SELECT id FROM p4c.task WHERE titolo=$1";
$done = pg_query_params($connection, $query, array($title));
$id = pg_fetch_row($done);

foreach ($keywords as $keyword){
    $query = "INSERT INTO p4c.contains_keyword(task, keyword) VALUES ($1, $2)";
    $done = pg_query_params($connection, $query, array($id[0], $keyword));

}
$title = urlencode($title);
$header = "Location:/P4C/addResponse.php?task=".$title."&k=".$keywords."&id=".$id[0];
header($header);
