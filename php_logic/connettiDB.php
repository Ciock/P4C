<?php
/**
 * Created by IntelliJ IDEA.
 * User: Andrea
 * Date: 23/05/2018
 * Time: 13:49
 */
function connettiDB()
{
    $connection = pg_connect("host=localhost dbname=postgres user=postgres password=postgres");
    if ($connection == null) {
        echo
        "<script>
        alert('Error during the connection');
    </script>";
    }
    return $connection;
}