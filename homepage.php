<!DOCTYPE html>
<html lang="en">
<style>
    * {
    box-sizing: border-box;
}
.column {
    float: left;
    width: 50%;
    padding: 10px;
}
.rowmine:after {
    content: "";
    display: table;
    clear: both;
}
</style>
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
                    $isAdmin = pg_query_params($connection, "SELECT * FROM p4c.admin WHERE admin_name = $1", array($_SESSION['login_user']));
                    $isAdmin = pg_fetch_row($isAdmin);
                    if ($isWorker) {
                        echo '
                        <li class="nav-item">
                            <a class="nav-link">';
                        echo $_SESSION['login_user'];
                        echo '
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stats.php">Profile</a>
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
                            <a class="nav-link" href="newCampaign.php">New Campaign</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="newTask.php">New Task</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="php_logic/sessionClose.php">Logout
                                <span class=" sr-only">(current)</span>
                            </a>
                        </li>
                    ';
                    } else
                        if ($isAdmin) {
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
<div class="container rowmine">
    <?php
    if ($isWorker) {
        echo "
            <h1 style='text-align: center'>Task</h1>
            <div style='margin: auto; width: 50%; padding: 10px;'
        ";
        $result = pg_query_params($connection, "SELECT * FROM p4c.task_assignment($1);", array($_SESSION['login_user']));
        if ($result == null)
            echo "Fail during query";
        while ($t = pg_fetch_row($result)) {
            // echo pg_num_rows($result);
            $res = pg_query_params($connection, "SELECT * FROM p4c.task WHERE id=$1;", array($t[0]));
            $task = pg_fetch_row($res);
            $doubleResponse = pg_query_params($connection,"SELECT * FROM p4c.made_response JOIN p4c.response ON made_response.response = id WHERE task = $1 AND worker = $2",array($t[0],$_SESSION['login_user']));
            $isdoubleResponse = pg_fetch_row($doubleResponse);
            if (pg_num_rows($doubleResponse) == 0) {
                echo "
                    <!-- Page Heading -->
                    <div class=\"row\">
                        <div class=\"col-lg-12 col-sm-12 portfolio-item\">
                            <div class=\"card h-100\">
                                <div class=\"card-body\">
                                    <div style='text-align: center' class=\"card-title\">
                                        <form id=\'myform\' method='GET' action='chooseResponse.php'>
                                           <input type='hidden' name='task' value=$task[0]>
                                           <h4 class=\"card-title\">$task[1]</h4>
                                           <input type='submit' value='Vedi Risposte'/>
                                        </form>
                                    </div>
                                <p class=\"card-text\"> <strong>Descrizione:</strong> $task[2]</p>
                                <h6 class=\"card-text\"><strong>Requester:</strong> $task[6]</h6 >
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
                    
                ";
            }
        }
        echo "</div>";
    } else if ($isRequester) {
        $query = "SELECT validated FROM p4c.requester WHERE username = $1";
        $result = pg_query_params($connection, $query, array($_SESSION['login_user']));
        $val = pg_fetch_result($result, 0, 0);
        if ($val == 'f') {
            echo "Wait until you're validated to the system!";
        }else{
            echo "
                <div class='column'>        
                    <h1>Campaigns</h1>";
            $result = pg_query_params($connection, "SELECT * FROM p4c.campaign AS C WHERE C.requester = $1;", array($_SESSION['login_user']));
            if ($result == null) {
                echo "Fail during query";
            }
            while ($row = pg_fetch_row($result)) {
                $fetch = urlencode($row[0]);
                echo "
                    <div class=\"row\">
                        <div class=\"col-lg-9 col-sm-9 portfolio-item\">
                            <div class=\"card h-100\">
                                <div class=\"card-body\">
                                    <div style='text-align: center' class=\"card-title\">
                                        <form id='myform' method='GET' action='report.php'>
                                            <input type='hidden' name='campaign' value=$fetch>
                                            <h4 class=\"card-title\">$row[0]</h4>
                                            <input type='submit' value='Report'/>
                                        </form>
                                    </div>
                                <p class=\"card-text\"> <strong>Opening Date: </strong>$row[2]</p>
                                <h6 class=\"card-text\"><strong>Registration Deadline: </strong>$row[3]</h6 >
                                </div>
                            </div>
                        </div>
                    </div>";
            }
            echo "
                </div>
                <div class='column'>
                    <h1>Ended Campaigns</h1>";
            $query = "SELECT * FROM p4c.campaign AS C WHERE (now()::date NOT BETWEEN C.opening_date AND C.registration_deadline_date) AND C.requester = $1;";
            // TODO: TUTTI I TASK DELLA CAMPAGNA VANNO MESSI A F!
            $result = pg_query_params($connection, $query, array($_SESSION['login_user']));
            if ($result == null) {
                echo "<div class=\"row\">
                        <div class=\"col-lg-9 col-sm-9 portfolio-item\">
                            <div class=\"card h-100\">
                                <div class=\"card-body\">
                                    <h4 class=\"card-title\">No ended Campaign</h4>
                                </div>
                            </div>
                        </div>
                     </div>
                 ";
            }
            while ($row = pg_fetch_row($result)) {
                $fetch = urlencode($row[0]);
                echo "
                    <div class=\"row\">
                        <div class=\"col-lg-9 col-sm-9 portfolio-item\">
                            <div class=\"card h-100\">
                                <div class=\"card-body\">
                                    <div style='text-align: center' class='card-title'>
                                        <form id='myform' method='GET' action='report.php'>
                                            <input type='hidden' name='campaign' value=$fetch>
                                            <h4 class=\"card-title\">$row[0]</h4>
                                            <input type='submit' value='Report'/>
                                        </form>
                                    </div>
                                <h6 class=\"card-text\"><strong>Registration Deadline:</strong>$row[3]</h6 >
                                </div>
                            </div>
                        </div>
                    </div>";
            }
            echo "</div>";
        }
    } else if ($isAdmin) {
        echo "<h1 class=\"my-4\">New Requesters</h1>";
        $result = pg_query_params($connection, "SELECT username FROM p4c.requester WHERE validated = $1", array('f'));
        if (empty($result)) {
            echo "No new requesters to be validated";
        } else {
            while ($row = pg_fetch_row($result)) {
                $fetch = urlencode($row[0]);
                echo "
                <div class=\"row\">
                    <div class=\"col-lg-9 col-sm-9 portfolio-item\">
                        <div class=\"card h-100\">
                            <div class=\"card-body\">
                            <form id='myform' method='GET' action='php_logic/validateUsers.php'>
                                <input type='hidden' name='requester' value=$fetch>
                                <h4 class=\"card-title\">$row[0]</h4>
                                <input type='submit' value='Accept'/>
                            </form>
                            <form id='myform' method='GET' action='php_logic/rejectUser.php'>
                                <input type='hidden' name='requester' value=$fetch>
                                <input type='submit' value='Reject'/>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>";
            }
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