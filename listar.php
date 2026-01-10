<?php
include("conexion.php");
$result = $conn->query("SELECT * FROM pedidos ORDER BY fecha DESC");
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

    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['codigo'] ?></td>
        <td><?= $row['cliente'] ?></td>
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
