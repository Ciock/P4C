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
<img src="immagini\immagineDBHomepage.jpg" alt="Immagine DB" width="100%">
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
    if ($isWorker) {
        echo "<h1 class=\"my-4\">Task</h1>";
        $result = pg_query_params($connection, "SELECT * FROM p4c.task_assignment($1);", array($_SESSION['login_user']));
        if ($result == null)
            echo "Fail during query";
        $row = pg_fetch_row($result);
        $simbols = array("{", "}");
        $tasks = explode(',', str_replace($simbols, "",$row[0] ));
        foreach ($tasks as $t) {
            $res = pg_query_params($connection, "SELECT * FROM p4c.task WHERE id=$1;", array($t));
            $task = pg_fetch_row($res);
            echo "
                    <!-- Page Heading -->
                    <div class=\"row\">
                        <div class=\"col-lg-4 col-sm-6 portfolio-item\">
                            <div class=\"card h-100\">
                                <div class=\"card-body\">
                                    <form id=\'myform\' method='GET' action='chooseResponse.php'>
                                       <input type='hidden' name='task' value=$task[0]>
                                       <h4 class=\"card - title\">$task[1]</h4>
                                       <input type='submit' value='Vedi Risposte'/>
                                    </form>
                                <p class=\"card-text\"> <strong>Descrizione:</strong> $task[2]</p>
                                <h6 class=\"card-text\"><strong>Requester:</strong> $task[6]</h6 >
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
                    
";
        }

    } else if ($isRequester) {
        echo "<h1 class=\"my-4\">Campagna</h1>";
        $result = pg_query_params($connection, "SELECT * FROM p4c.campaign WHERE requester = $1;", array($_SESSION['login_user']));
        if ($result == null)
            echo "Fail during query";
        while ($row = pg_fetch_row($result)) {
            $fetch = urlencode($row[0]);
            echo "
                <div class=\"row\">
                    <div class=\"col-lg-4 col-sm-6 portfolio-item\">
                        <div class=\"card h-100\">
                            <div class=\"card-body\">
                            <form id='myform' method='GET' action='tasks.php'>
                                <input type='hidden' name='campaign' value=$fetch>
                                <h4 class=\"card-title\">$row[0]</h4>
                                <input type='submit' value='Vedi Task'/>
                            </form>
                            <p class=\"card-text\"> <strong>Data inizio:</strong>$row[2]</p>
                            <h6 class=\"card-text\"><strong>Data fine:</strong>$row[3]</h6 >
                            </div>
                        </div>
                    </div>
                </div>";
        }
    } else {

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
