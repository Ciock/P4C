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
                    $isWorker = pg_query_params($connection, "SELECT * FROM p4c.worker WHERE username = $1", array($_SESSION['login_user']));
                    $isWorker = pg_fetch_row($isWorker);
                    $isRequester = pg_query_params($connection, "SELECT * FROM p4c.requester WHERE username = $1", array($_SESSION['login_user']));
                    $isRequester = pg_fetch_row($isRequester);
                    if ($isWorker) {
                        echo '
                        <li class="nav-item">
                            <a class="nav-link">';
                        echo $_SESSION['login_user'];
                        echo '
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stats.php">Stats</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="php_logic/sessionClose.php">Logout
                                <span class=" sr-only">(current)</span>
                            </a>
                        </li>
                    ';
                    } else if ($isRequester) {
                        echo '
                        <li class="nav-item">
                            <a class="nav-link">';
                        echo $_SESSION['login_user'];
                        echo '
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stats.php">Report</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="newCampaign.php">New Campaign</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="newTask.php">New Tasks</a>
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
                        <li class="nav-item">
                            <a class="nav-link">';
                        echo $_SESSION['login_user'];
                        echo '
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="php_logic/sessionClose.php">Logout
                                <span class=" sr-only">(current)</span>
                            </a>
                        </li>
                    ';
                    }
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
<div class="container">
    <?php
    $campaign = $_REQUEST['campaign'];
    $campaign = urldecode($campaign);
            echo "<h1 class=\"my-4\">Task</h1>";
            $result = pg_query_params($connection, "SELECT * FROM p4c.task WHERE campaign = $1;", array($campaign));
            if ($result == null)
                echo "Fail during query";
            while ($row = pg_fetch_row($result)) {
                $fetch = urlencode($row[1]);
                echo "
                <div class=\"row\">
                    <div class=\"col - lg - 4 col - sm - 6 portfolio - item\">
                        <div class=\"card h - 100\">
                            <div class=\"card - body\">
                                <h4 class=\"card - title\">
                                    <form id='myform' method='GET' action='chooseResponse.php'>
                                       <input type='hidden' name='task' value=$row[0]>
                                       <h4 class=\"card - title\">$row[1]</h4>
                                       <input type='submit' value='Vedi Risposte'/>
                                    </form>
                                </h4 >
                            <p class=\"card-text\" > <strong > Descrizione:</strong > $row[2]</p >
                            <p class=\"card-text\" > <strong > Soglia di maggioranza:</strong > $row[4]</p >
                            <p class=\"card-text\" > <strong > Risultato:</strong > $row[5]</p >
                            </div >
                        </div >
                    </div >
                </div >";
            }
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
