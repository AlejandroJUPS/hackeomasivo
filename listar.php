<?php
include("conexion.php");
$pedidos = obtenerPedidos();
// Ordenar por fecha descendente
usort($pedidos, function($a, $b) {
    return strtotime($b['fecha']) - strtotime($a['fecha']);
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de pedidos</title>
</head>
<body>

<h2>Pedidos Registrados</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>Código</th>
        <th>Cliente</th>
        <th>Pastel Sencillo</th>
        <th>Pastel Especial</th>
        <th>Fecha</th>
        <th>Acción</th>
    </tr>

    <?php foreach ($pedidos as $row) { ?>
    <tr>
        <td><?= htmlspecialchars($row['codigo']) ?></td>
        <td><?= htmlspecialchars($row['cliente']) ?></td>
        <td><?= $row['cantidad_sencillo'] ?></td>
        <td><?= $row['cantidad_especial'] ?></td>
        <td><?= $row['fecha'] ?></td>
        <td>
            <a href="eliminar.php?id=<?= $row['id'] ?>">Eliminar</a>
        </td>
    </tr>
    <?php } ?>

</table>

<br>
<a href="index.php">Nuevo pedido</a>

</body>
</html>
