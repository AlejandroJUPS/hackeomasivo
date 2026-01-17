<?php
if (!isset($_GET['system'], $_GET['rom'])) {
    die("Juego no especificado");
}

$system = preg_replace('/[^a-z0-9_]/i', '', $_GET['system']);
$rom = basename(urldecode($_GET['rom']));

$romPath = "roms/$system/$rom";

if (!file_exists($romPath)) {
    die("La ROM no existe");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>System Beware Retro — Play</title>

<style>
html, body {
    margin: 0;
    width: 100%;
    height: 100%;
    background: black;
    overflow: hidden;
}
#display {
    width: 100%;
    height: 100%;
}
#game {
    width: 100%;
    height: 100%;
}
</style>
</head>

<body>

<div id="display">
    <div id="game"></div>
</div>

<script>
/* CONFIGURACIÓN EXACTA COMO EL PROYECTO QUE FUNCIONA */

window.EJS_player = "#game";
window.EJS_gameName = "<?= pathinfo($rom, PATHINFO_FILENAME) ?>";
window.EJS_gameUrl = "<?= $romPath ?>";
window.EJS_core = "<?= $system ?>";

/* ESTA ES LA CLAVE */
window.EJS_pathtodata = "data/";
window.EJS_startOnLoaded = true;

/* CARGA CORRECTA */
const script = document.createElement("script");
script.src = "data/loader.js";
document.body.appendChild(script);
</script>

</body>
</html>