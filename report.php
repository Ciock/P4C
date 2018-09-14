<!DOCTYPE html>
<html lang="en">

<head>

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

<?php
include 'php_logic/connettiDB.php';
$connection = connettiDB();
session_start();
?>

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
                    $isRequester = pg_query_params($connection, "SELECT * FROM p4c.requester WHERE username = $1", array($_SESSION['login_user']));
                    $isRequester = pg_fetch_row($isRequester);
                    if ($isRequester) {
                        echo '
                        <li class="nav-item">
                            <a class="nav-link">';
                        echo $_SESSION['login_user'];
                        echo '
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="newCampaign.php">New Campaign</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="newTask.php">New Task</a>
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
                }
                ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<div class="container">
    <?php
    $campaign = $_REQUEST['campaign'];
    $campaign = urldecode($campaign);
    $result = pg_query_params($connection, "SELECT count(*) FROM p4c.task WHERE campaign = $1;", array($campaign));
    $row = pg_fetch_row($result);
    $numTask = $row[0];
    $result = pg_query_params($connection, "SELECT count(*) FROM p4c.task WHERE campaign = $1 AND result IS TRUE ;", array($campaign));
    $row = pg_fetch_row($result);
    $numTaskValidi = $row[0];
    $ratioTask = ($numTaskValidi/($numTask*1.0))*100;
    $ratioTask = number_format($ratioTask, 2, ',', '');

    echo "
                <h1 class=\"my-4\">Created Task</h1> <h2> $numTask</h2>
                <h1 class=\"my-4\">Completed Task</h1> <h2> $numTaskValidi</h2>       
                <h1 class=\"my-4\">Ratio Task</h1> <h2> $ratioTask%</h2>  
            ";
    }
    ?>
    <?php
    echo "
    <form method=\"get\" action=\"php_logic/top10.php\">
        <input type=\"submit\" value=\"top10\">
        <input type=\"hidden\" value=$campaign>
    </form>
    ";
    ?>
</div>
<!-- /.container -->
<!-- Footer -->
<footer class="py-5 bg-dark">
    <div class="container">
        <p class="m-0 text-center text-white">Copyright &copy; Kappa 2018</p>
    </div>
    <!-- /.container -->
</footer>

<!-- Bootstrap core JavaScript -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="/js/bootstrap.bundle.min.js"></script>

</body>

</html>
