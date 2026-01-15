<?php
include("conexion.php");

$codigo    = $_POST['codigo'] ?? '';
$cliente   = $_POST['cliente'] ?? '';
$sencillo  = $_POST['sencillo'] ?? 0;
$especial  = $_POST['especial'] ?? 0;

if (!empty($codigo) && !empty($cliente)) {
    agregarPedido($codigo, $cliente, $sencillo, $especial);
    header("Location: listar.php");
} else {
    header("Location: index.php");
}
?>
