<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Pastelería</title>
</head>
<body>

<h2>Nuevo Pedido</h2>

<form action="guardar.php" method="POST">
    <label>Código del pedido:</label><br>
    <input type="text" name="codigo" required><br><br>

    <label>Nombre del cliente:</label><br>
    <input type="text" name="cliente" required><br><br>

    <label>Cantidad pastel sencillo:</label><br>
    <input type="number" name="sencillo" min="0" value="0" required><br><br>

    <label>Cantidad pastel especial:</label><br>
    <input type="number" name="especial" min="0" value="0" required><br><br>

    <button type="submit">Guardar Pedido</button>
</form>

<br>
<a href="listar.php">Ver pedidos</a>

</body>
</html>
