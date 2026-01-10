<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos | Pastelería</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        body {
            margin: 0;
            background: linear-gradient(135deg, #0f0f0f, #1c1c1c);
            color: #eaeaea;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background-color: #1f1f1f;
            padding: 30px 40px;
            border-radius: 10px;
            width: 380px;
            box-shadow: 0 0 20px rgba(0,0,0,0.6);
        }

        .card h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #f2c94c;
            letter-spacing: 1px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #bdbdbd;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            background-color: #2a2a2a;
            border: 1px solid #333;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #f2c94c;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #f2c94c;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #d4ac2b;
        }

        .link {
            text-align: center;
            margin-top: 18px;
        }

        .link a {
            color: #9aa0a6;
            text-decoration: none;
            font-size: 14px;
        }

        .link a:hover {
            color: #f2c94c;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Nuevo Pedido</h2>

    <form action="guardar.php" method="POST">

        <label>Código del pedido</label>
        <input type="text" name="codigo" required>

        <label>Nombre del cliente</label>
        <input type="text" name="cliente" required>

        <label>Pastel sencillo</label>
        <input type="number" name="sencillo" min="0" value="0" required>

        <label>Pastel especial</label>
        <input type="number" name="especial" min="0" value="0" required>

        <button type="submit">Guardar pedido</button>
    </form>

    <div class="link">
        <a href="listar.php">Ver pedidos registrados</a>
    </div>
</div>

</body>
</html>
