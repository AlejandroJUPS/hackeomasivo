<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Emulador SNES</title>
</head>
<body>

<h2>Emulador SNES - EmulatorJS</h2>
<p>Copia tu ROM SNES (.sfc o .smc) en la carpeta <b>roms</b> con el nombre <b>juego.sfc</b></p>

<div id="game"></div>

<script>
    EJS_player = "#game";
    EJS_core = "snes";
    EJS_gameUrl = "roms/juego.sfc";
    EJS_pathtodata = "data/";
</script>

<script src="data/loader.js"></script>

</body>
</html>
