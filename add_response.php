<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    include 'php_logic/connettiDB.php';
    $connection = connettiDB();
    session_start();
    ?>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>P4C Website</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/3-col-portfolio.css" rel="stylesheet">

</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <?php
                if ($_SESSION['login_user']) {
                    echo '
                        <li class="nav-item">
                            <a class="nav-link">';
                    echo $_SESSION['login_user'];
                    echo '
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="homepage.php">Homepage</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="php_logic/sessionClose.php">Logout
                                <span class=" sr-only">(current)</span>
                            </a>
                        </li>
                    ';
                } else {
                    echo '
                        <li class="nav-item active">
                            <a class="nav-link" href="registration.php">Registration
                                <span class=" sr-only">(current)</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    ';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<?php
$title = $_REQUEST['task'];
$title = urlencode($title);
echo " 
    <div class=\"container\">
    <h1 class=\"my - 4\">Add Response</h1>
    <form action=\"php_logic/addResponse.php\" method=\"get\">
            <input type=\"hidden\" name=\"task\" value=$title>
            <input type=\"text\" name=\"response\" placeholder=\"Response\">
            <input type=\"submit\" name=\"button\" value=\"Add\">
            <input type=\"submit\" name=\"button\" value=\"Back to homepage\">
    </form>
    </div>";
?>

<!-- Footer -->
<footer class="py-5 bg-dark">
    <div class="container">
        <p class="m-0 text-center text-white">Copyright &copy; P4C 2018</p>
    </div>
    <!-- /.container -->
</footer>

<!-- Bootstrap core JavaScript -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="/js/bootstrap.bundle.min.js"></script>

</body>

</html>
