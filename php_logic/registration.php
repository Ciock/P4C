<?php
include "connettiDB.php";
$connection = connettiDB();
switch ($_REQUEST['registrationbutton']) {

    // TODO: funzione per effettuare la registrazione
    case 'sign in': //action for html here
        //header("Location:/P4C/registration.php");
        $name = $_REQUEST['name'];
        $password = sha1($_REQUEST['password']);
        $passwordconf = sha1($_REQUEST['passwordconf']);
        $role = $_REQUEST['role'];
        echo $role;
        if ($password == $passwordconf) {
            if ($role == "choice1") {
                pg_query_params($connection, "INSERT INTO p4c.user(username, password) VALUES ($1, $2)", array($name, $password));
                pg_query_params($connection, "INSERT INTO p4c.requester(username) VALUES ($1)", array($name));
                echo "<script>
                    alert('Successful');
                    window.location.href='../homepage.php';
                    </script>";
            } elseif ($role == "choice2") {
                pg_query_params($connection, "INSERT INTO p4c.user(username, password) VALUES ($1, $2)", array($name, $password));
                pg_query_params($connection, "INSERT INTO p4c.worker(username) VALUES ($1)", array($name));
                echo "
                <script>
                    alert('Successful');
                    window.location.href='../homepage.php';
                    </script>";
            } else {
                echo "<script>
                    alert('Select at least a role');
                    window.location.href='../registration.php';
                    </script>";
            }
        }
        echo "<script>
                    alert('Passwords do not match');
                    window.location.href='../registration.php';
                    </script>";
        break;

    case 'login': //action for html here
        header("Location:/P4C / login . php");
        break;

    default:
        echo
        " < script>
            alert('qualcosa è andato storto');
            window . location . href = '../registration.php';
            </script > ";
}