<!DOCTYPE html>
<html lang="en">
<style>
    table{
        margin-bottom: 30px;
        width:100%;
        border-bottom: 2px solid black;
        border-top: 2px solid black;
    }
    th, td {
        border-bottom: 1px solid #ddd;
        padding: 15px;
        text-align: left;
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
$result = pg_query_params($connection, "SELECT * FROM p4c.stats($1);", array($_SESSION['login_user']));
$skills = pg_query_params($connection, "SELECT * FROM p4c.got_skills WHERE worker=$1;", array($_SESSION['login_user']));
if ($result == null)
    echo "Fail during query";
$row = pg_fetch_row($result);
echo "
    <div style='margin: auto; width: 50%; padding: 10px;'>
    <div class=\"container\">
        <h2 class=\"my-4\">Position: $row[2]</h2>
        <h2 class=\"my-4\">Score: $row[1]</h2>
    </div>
    <table>
        <tr>
            <th>Skill</th> 
            <th>Score</th>
        </tr>
";

while ($skill = pg_fetch_row($skills)) {
    echo "
        <tr>
            <td>$skill[1]</td>
            <td>$skill[2]</td> 
        </tr>
    ";
    }
    echo "
</table>
<div class=\"container\">
    <!-- Page Heading -->
    <h2 class=\"my-4\">Task accepted</h2>";
    $removeParentesi = array("{", "}");
    $eseguitiCleared = str_replace($removeParentesi, "", $row[3]);
    $eseguitiArray = explode(',', $eseguitiCleared);
    foreach ($eseguitiArray as $item) {
        if($item){
        $item = str_replace("\"", "", $item);
        $task = pg_query_params($connection, "SELECT * FROM p4c.task WHERE titolo = $1;", array($item));
        $taskArray = pg_fetch_row(($task));
        echo "
        <div class=\"row\">
            <div class=\"col-lg-9 col-sm-9 portfolio-item\">
                <div class=\"card h-100\">
                    <div class=\"card-body\">
                        <input type='hidden' name='task' value=$taskArray[0]>
                        <h4 class=\"card-title\">$taskArray[1]</h4>
                        <p class=\"card-text\"> <strong>Description:</strong> $taskArray[2]</p>
                        <h6 class=\"card-text\"><strong>Requester:</strong> $taskArray[6]</h6 >
                    </div>
                </div>
            </div>
        </div>
<!-- /.row -->";
        }else{
            echo "<div><i>No terminated tasks</i></div>";
        }
    }
    echo "
</div>
<div class=\"container\">

<!-- Page Heading -->
<h2 class=\"my-4\">Task validated</h2>";
    $removeParentesi = array("{", "}");
    $eseguitiCleared = str_replace($removeParentesi, "", $row[4]);
    $eseguitiArray = explode(',', $eseguitiCleared);
    foreach ($eseguitiArray as $item) {
        if ($item){
        $item = str_replace("\"", "", $item);
        $taskValidi = pg_query_params($connection, "SELECT * FROM p4c.task WHERE titolo = $1;", array($item));
        $taskValidiArray = pg_fetch_row(($taskValidi));
        echo "
        <div class=\"row\">
            <div class=\"col-lg-9 col-sm-9 portfolio-item\">
                <div class=\"card h-100\">
                    <div class=\"card-body\">
                        <input type='hidden' name='task' value=$taskValidiArray[0]>
                        <h4 class=\"card-title\">$taskValidiArray[1]</h4>
                        <p class=\"card-text\"> <strong>Description:</strong> $taskValidiArray[2]</p>
                        <h6 class=\"card-text\"><strong>Requester:</strong> $taskValidiArray[6]</h6 >
                    </div>
                </div>
            </div>
        </div>";
        }else{
            echo "<div><i>No accepted task has finished</i></div>";
        }
    }
    echo "
<!-- /.row -->
</div>
</div>
<!-- /.container -->
";
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
