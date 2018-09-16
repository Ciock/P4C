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
                }
                ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
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
        <div class=\"container\">
            <table>
                <tr>
                    <th>Created Task</th>
                    <th>Completed Task</th>
                    <th>% Completed Task</th>
                </tr>
                <tr>
                    <td>$numTask</td>
                    <td>$numTaskValidi</td>
                    <td>$ratioTask%</td>
                </tr>
            </table> 
        </div>
    ";
//p4c.top10(CAMPAGNA, REQUESTER)
$result = pg_query_params($connection, "SELECT p4c.top10($1,$2);", array($_REQUEST['campaign'], $_SESSION['login_user']));

echo "
    <div class=\"container\">
        <table>
";
if ($result == null)
    echo "<caption>No one of campaign task has finished</caption>";
echo "
    <tr>
        <th></th>
        <th>Worker</th> 
        <th>Score</th>
    </tr>
";
$position = 1;
while ($row = pg_fetch_row($result)) {
    $removeParentesi = array("(", ")");
    $row[0] = str_replace($removeParentesi, "", $row[0]);
    $worker = explode(',', $row[0]);
    echo "
        <tr>
            <td>$position</td>
            <td>$worker[0]</td> 
            <td>$worker[1]</td>
        </tr>
    ";
    $position = $position + 1;
}
echo "
        </table>
    </div>
";
$result = pg_query_params($connection, "SELECT * FROM p4c.task WHERE campaign = $1;", array($campaign));
    if ($result == null)
        echo "Fail during query";
    while ($row = pg_fetch_row($result)) {
        $fetch = urlencode($row[1]);
        $campaign = urlencode($campaign);
        echo "
        <div class=\"container\">
            <div style='margin: auto; width: 50%; padding: 10px;' class=\"row\">
                <div class=\"col-lg-9 col-sm-9 portfolio-item\">
                    <div class=\"card h-100\">
                        <div class=\"card-body\">
                            <div class=\"card-title\" style='text-align: center'>
                                <form id='myform' method='GET' action='chooseResponse.php'>
                                   <input type='hidden' name='task' value=$row[0]>
                                   <input type='hidden' name='campaign' value=$campaign>
                                   <h4 class=\"card-title\">$row[1]</h4>
                                   <input type='submit' value='See responses'/>
                                </form>
                            </div >
                        <p class=\"card-text\" > <strong > Description:</strong > $row[2]</p >
                        <p class=\"card-text\" > <strong > Majority Threshold:</strong > $row[4]</p > ";
                        if($row[5] == 't')
                            echo "<p style='text-align: right' class=\"card-text\"> <strong><i>Majority Achieved!</i></strong></p> ";
                        elseif ($row[5] == 'f')
                            echo "<p style='text-align: right' class=\"card-text\" > <strong><i>Finished without reaching the majority</i></strong></p > ";
                        else
                            echo "<p style='text-align: right' class=\"card-text\" > <strong><i>Still working on it</i></strong></p > ";

                        echo "</div >
                    </div >
                </div >
            </div >
        </div>
        ";
    }
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
