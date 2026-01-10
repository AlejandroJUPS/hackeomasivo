<?php
include("conexion.php");

$codigo    = $_POST['codigo'];
$cliente   = $_POST['cliente'];
$sencillo  = $_POST['sencillo'];
$especial  = $_POST['especial'];

$sql = "INSERT INTO pedidos (codigo, cliente, cantidad_sencillo, cantidad_especial)
        VALUES ('$codigo', '$cliente', $sencillo, $especial)";

if ($conn->query($sql) === TRUE) {
    header("Location: listar.php");
} else {
    echo "Error: " . $conn->error;
}
?>
