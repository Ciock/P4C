<?php
    switch($_REQUEST['registrationbutton']) {

        // TODO: funzione per effettuare la registrazione
        case 'sign in': //action for html here
            header("Location:/P4C/registration.php");
            break;

        case 'login': //action for html here
            header("Location:/P4C/login.php");
            break;

        default:
            echo
            "<script>
            alert('qualcosa è andato storto');
            window.location.href='../registration.php';
            </script>";
    }