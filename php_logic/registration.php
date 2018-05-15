<?php
    switch($_REQUEST['registrationbutton']) {

        // TODO: funzione per effettuare la registrazione
        case 'registration': //action for html here
            header("Location:/P4C/registration.php");
            break;

        case 'homepage': //action for html here
            header("Location:/P4C/homepage.php");
            break;

        default:
            echo
            "<script>
            alert('qualcosa è andato storto');
            window.location.href='../registration.php';
            </script>";
    }