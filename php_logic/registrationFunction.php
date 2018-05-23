<?php
include "connettiDB.php";
$connection = connettiDB();
switch ($_REQUEST['registrationbutton']) {

    // TODO: funzione per effettuare la registrazione
    case 'sign in':
        $name = $_REQUEST['name'];
        $password = sha1($_REQUEST['password']);
        $passwordconf = sha1($_REQUEST['passwordconf']);
        $role = $_REQUEST['role'];
        $skillz = $_REQUEST['skills[]'];
        if ($password == $passwordconf) {
            if ($role == "requester") {
                pg_query_params($connection, "INSERT INTO p4c.user(username, password) VALUES ($1, $2)", array($name, $password));
                pg_query_params($connection, "INSERT INTO p4c.requester(username) VALUES ($1)", array($name));
                echo "<script>
                    alert('Successful');
                    window.location.href='../homepage.php';
                    </script>";
            } elseif ($role == "worker") {
                pg_query_params($connection, "INSERT INTO p4c.user(username, password) VALUES ($1, $2)", array($name, $password));
                pg_query_params($connection, "INSERT INTO p4c.worker(username) VALUES ($1)", array($name));
                $i = 0;
                foreach ($skillz as $i) {
                    pg_query_params($connection, "INSERT INTO p4c.got_skills(worker, skill) VALUES ($1,$2)", array($name,$i));
                }
                echo "
                <script>
                    alert('Successful');
                    window.location.href='../homepage.php';
                    </script>";
            } else {
                echo "<script>
                    alert('Select one role');
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
        header("Location:/P4C/login.php");
        break;

    default:
        echo
        " < script>
            alert('qualcosa è andato storto');
            window.location.href = '../registration.php';
            </script > ";
}