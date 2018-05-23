<!DOCTYPE html>
<html class=''>
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
    <link href="css/registration.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- SOURCE: https://bootsnipp.com/snippets/dldxB -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
</head>

<body>

<?php
include 'php_logic/connettiDB.php';
$connection = connettiDB();
?>

<div class="wrapper fadeInDown">
    <div id="formContent">
        <!-- Tabs Titles -->

        <!-- Icon -->
        <div class="fadeIn first">
            <img src="http://danielzawadzki.com/codepen/01/icon.svg" id="icon" alt="User Icon"/>
        </div>

        <!-- Login Form -->
        <form action="php_logic/registration.php">
            <input type="text" id="login" class="fadeIn second" name="name" placeholder="Login">
            <input type="password" id="password" class="fadeIn third" name="password" placeholder="Password">
            <input type="password" id="conf_password" class="fadeIn fourth" name="passwordconf" placeholder="Confirm password">
            <select class="js-example-basic-multiple fadeIn second" name="states[]" multiple="multiple">
                <?php
                $result = pg_query($connection, "select * from p4c.skills");
                if ($result == null)
                    echo "Fail during query";
                while ($row = pg_fetch_row($result)) {
                    echo "<option>$row[0]</option>";
                }
                ?>

            </select>

            <label class="container fadeIn fifth">I'm a worker!
                <input type="radio" id="choice1" name="isWorker">
                <span class="checkmark"></span>
            </label>

            <label class="container fadeIn fifth">I'm a requester!
                <input type="radio" id="choice2" name="isRequester">
                <span class="checkmark"></span>
            </label>
            <input type="submit" class="fadeIn fifth" name="registrationbutton" value="sign in">
            <input type="submit" class="fadeIn fifth" name="registrationbutton" value="login">
        </form>

    </div>
</div>
<script>
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2();
    });


</script>
</body>
</html>