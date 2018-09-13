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
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<div class=\"container\">
    <h1 class=\"my-4\">New Task</h1>
    <form action="php_logic/validateUsers.php" method="get">
            <select name="campaign">
                <?php
                $result = pg_query_params($connection, "SELECT username FROM p4c.requester WHERE validated = $1", array(False));
                while ($row = pg_fetch_row($result)) {
                    echo "<option>$row[0]</option>";
                }
                ?>
            </select>
            <input type="submit" name="button" value="Validate">
    </form>
</div>

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
