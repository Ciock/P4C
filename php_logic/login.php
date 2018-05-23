<?php
include "connettiDB.php";
$connection = connettiDB();
switch ($_REQUEST['loginbutton']) {

    // TODO: funzione per effettuare il login
    case 'login': //action for html here
        $name = $_REQUEST['name'];
        $password = sha1($_REQUEST['password']);
        $result = pg_query_params($connection, "SELECT * FROM p4c.user WHERE username=$1 and password=$2", array($name, $password));
        if (pg_num_rows($result)>0) {
            session_start();
            // Store Session Data
            $_SESSION['login_user'] = $name;  // Initializing Session with value of PHP Variable
            header("Location:/P4C/homepage.php");
        }
        echo
        "<script>
            alert('username o password errati');
            window.location.href='../login.php';
            </script>";
        break;

    case 'sign in': //action for html here
        header("Location:/P4C/registration.php");
        break;

    default:
}