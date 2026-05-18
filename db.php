<?php

$host = "sql206.infinityfree.com";
$user = "if0_41949366";
$pass = "o1z4riVIZM6ui";
$db   = "if0_41949366_emutable";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>