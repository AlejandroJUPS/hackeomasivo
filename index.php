<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Emulador SNES</title>

    <style>
        body {
            background: #0f172a;
            color: #e5e7eb;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h1 {
            margin-top: 20px;
        }

        #game {
            width: 640px;
            height: 480px;
            margin: 20px auto;
            background: black;
        }
    </style>
</head>
<body>

<h1>Emulador SNES</h1>
<p>ROM cargado desde la carpeta <b>roms/</b></p>

<div id="game"></div>

<script>
    // CONTENEDOR DEL EMULADOR
    EJS_player = "#game";

    // CORE DE LA CONSOLA
    EJS_core = "snes";

    // RUTA DEL ROM (ESTÁ BIEN EN TU PROYECTO)
    EJS_gameUrl = "roms/juego.sfc";

    // RUTA A LA CARPETA data/
    EJS_pathtodata = "data/";

    // OPCIONAL
    EJS_language = "es";
</script>

<!-- ARCHIVO CLAVE DEL EMULADOR -->
<script src="data/src/emulator.js"></script>

</body>
</html>
