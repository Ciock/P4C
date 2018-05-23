<?php
    switch($_REQUEST['loginbutton']) {

        // TODO: funzione per effettuare il login
        case 'login': //action for html here
            header("Location:/P4C/login.php");
            break;

        case 'sign in': //action for html here
            header("Location:/P4C/registration.php");
            break;

        default:
            echo
            "<script>
            alert('username o password errati');
            window.location.href='../login.php';
            </script>";
    }