<?php
include "connettiDB.php";
$connection = connettiDB();
switch ($_POST['loginbutton']) {
    case 'login': //action for html here
        $name = $_POST['name'];
        $password = sha1($_POST['password']);
        $result = pg_query_params($connection, "SELECT * FROM p4c.user WHERE username=$1 and password=$2", array($name, $password));
        if (pg_num_rows($result)>0) {
            session_start();
            // Store Session Data
            $_SESSION['login_user'] = $name;  // Initializing Session with value of PHP Variable
            header("Location:/P4C/homepage.php");
        }
        echo
        "<script>
            alert('Incorrect username or password');
            window.location.href='../login.php';
            </script>";
        break;

    case 'register': //action for html here
        header("Location:/P4C/registration.php");
        break;

    default:
}