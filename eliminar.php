<?php
include("conexion.php");

$id = $_GET['id'] ?? null;

if ($id !== null) {
    eliminarPedido($id);
}

header("Location: listar.php");
?>
