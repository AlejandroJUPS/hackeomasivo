<?php
include("conexion.php");

$id = $_GET['id'];

$conn->query("DELETE FROM pedidos WHERE id = $id");

header("Location: listar.php");
?>
