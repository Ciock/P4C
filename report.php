<!DOCTYPE html>
<html lang="en">
<style>
    table{
        padding: 30px;
        width:100%;
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
                }
                ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<?php
//p4c.top10(CAMPAGNA, REQUESTER)
$result = pg_query_params($connection, "SELECT p4c.top10($1,$2);", array($_REQUEST['campaign'], $_SESSION['login_user']));

echo "
    <table>
";
if ($result == null)
    echo "<caption>No one answered</caption>";
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