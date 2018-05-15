<!DOCTYPE html>
<html class=''>
<head>
    <link href="css/registration.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- SOURCE: https://bootsnipp.com/snippets/dldxB -->

</head>

<body>

<div class="wrapper fadeInDown">
    <div id="formContent">
        <!-- Tabs Titles -->

        <!-- Icon -->
        <div class="fadeIn first">
            <img src="http://danielzawadzki.com/codepen/01/icon.svg" id="icon" alt="User Icon" />
        </div>

        <!-- Login Form -->
        <form action="php_logic/registration.php">
            <input type="text" id="login" class="fadeIn second" name="login" placeholder="Login">
            <input type="password" id="password" class="fadeIn third" name="login" placeholder="Password">
            <input type="password" id="conf_password" class="fadeIn fourth" name="login" placeholder="Confirm password">

            <label class="container">I'm a worker!
                <input type="radio" id = "choice1" name="choice1">
                <span class="checkmark"></span>
            </label>

            <label class="container">I'm a requester!
                <input type="radio" id = "choice2" name="choice1">
                <span class="checkmark"></span>
            </label>

            <br/>

            <input type="submit" class="fadeIn fifth" name="registrationbutton" value="registration">
            <input type="submit" class="fadeIn fifth" name="registrationbutton" value="homepage">
        </form>

    </div>
</div>
</body>
</html>