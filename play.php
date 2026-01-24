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

require_once "caratulas.php";
$caratulaPath = getCaratulaPath($system, $rom);
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
    font-family: Arial, sans-serif;
}
.container {
    display: flex;
    width: 100%;
    height: 100%;
}
#display {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
#game {
    width: 100%;
    height: 100%;
}
.caratula-sidebar {
    width: 220px;
    background: #1a1a1a;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    border-left: 2px solid #333;
    overflow-y: auto;
}
.caratula-image {
    width: 180px;
    max-height: 250px;
    object-fit: cover;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.5);
    margin-bottom: 20px;
}
.game-title {
    color: #fff;
    font-size: 13px;
    text-align: center;
    word-wrap: break-word;
    margin-bottom: 15px;
}
.back-button {
    background: #e53935;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    transition: .2s;
    font-size: 13px;
}
.back-button:hover {
    background: #c62828;
}
</style>
</head>

<body>

<div class="container">
    <div id="display">
        <div id="game"></div>
    </div>
    
    <div class="caratula-sidebar">
        <img src="<?= htmlspecialchars($caratulaPath) ?>" alt="Caratula" class="caratula-image">
        <div class="game-title"><?= htmlspecialchars(pathinfo($rom, PATHINFO_FILENAME)) ?></div>
        <button class="back-button" onclick="window.history.back()">← Volver</button>
    </div>
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