<?php
/**
 * Created by IntelliJ IDEA.
 * User: Andrea
 * Date: 12/09/2018
 * Time: 17:47
 */

include "connettiDB.php";
$connection = connettiDB();
session_start();

$admin = $_SESSION['login_user'];
$requester = $_REQUEST['requester'];
$requester = urldecode($requester);

$query = "UPDATE p4c.requester SET validated = $1 WHERE username = $2;";

$done = pg_query_params($connection, $query, array('f',$requester));

$query = "DELETE FROM p4c.user WHERE username = $1;";
$done = pg_query_params($connection, $query, array($requester));

header("Location:/P4C/homepage.php");