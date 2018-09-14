<!DOCTYPE html>
<html class=''>
<head>
    <link href="css/login.css" rel="stylesheet" id="bootstrap-css">
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
        <form method="POST" action="php_logic/login.php">
            <input type="text" id="login" class="fadeIn first" name="name" placeholder="Login">
            <input type="password" id="password" class="fadeIn second" name="password" placeholder="Password">
            <input type="submit" class="fadeIn third" name="loginbutton" value="login">
            <input type="submit" class="fadeIn third" name="loginbutton" value="register">
        </form>
    </div>
</div>
</body>
</html>