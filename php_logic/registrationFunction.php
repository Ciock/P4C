<?php
include "connettiDB.php";
$connection = connettiDB();
switch ($_REQUEST['registrationbutton']) {
    case 'register':
        $name = $_REQUEST['name'];
        $password = sha1($_REQUEST['password']);
        $passwordconf = sha1($_REQUEST['passwordconf']);
        $role = $_REQUEST['role'];
        $skills = $_REQUEST['skills'];
        if ($password == $passwordconf) {
            if ($role == "requester") {
                pg_query_params($connection, "INSERT INTO p4c.user(username, password) VALUES ($1, $2)", array($name, $password));
                pg_query_params($connection, "INSERT INTO p4c.requester(username) VALUES ($1)", array($name));
                session_start();
                // Store Session Data
                $_SESSION['login_user'] = $name;  // Initializing Session with value of PHP Variable
                header("Location:/P4C/homepage.php");
            } elseif ($role == "worker") {
                pg_query_params($connection, "INSERT INTO p4c.user(username, password) VALUES ($1, $2)", array($name, $password));
                pg_query_params($connection, "INSERT INTO p4c.worker(username) VALUES ($1)", array($name));
                $i = 0;
                foreach ($skills as $i) {
                    pg_query_params($connection, "INSERT INTO p4c.got_skills(worker, skill) VALUES ($1,$2)", array($name, $i));
                }
                session_start();
                // Store Session Data
                $_SESSION['login_user'] = $name;  // Initializing Session with value of PHP Variable
                header("Location:/P4C/homepage.php");
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
}