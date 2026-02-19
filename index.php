<?php
session_start();
require_once "db.php";
require_once "caratulas.php";

/* =========================
   DATOS DE SESIÓN
========================= */
$logged = isset($_SESSION['user_id']);
$avatar = $_SESSION['avatar'] ?? 'avatar1';
$username = $_SESSION['username'] ?? '';
$avatarPath = "assets/avatars/$avatar.jpg";

/* =========================
   SISTEMAS
========================= */
$systems=[
 "nes"=>["label"=>"NES","short"=>"NES","logo"=>"logo/nes.png", "color"=>"#E74C3C"],
 "snes"=>["label"=>"SNES","short"=>"SNES","logo"=>"logo/snes.png", "color"=>"#9B59B6"],
 "n64"=>["label"=>"Nintendo 64","short"=>"N64","logo"=>"logo/n64.png", "color"=>"#3498DB"],
 "gba"=>["label"=>"Game Boy Advance","short"=>"GBA","logo"=>"logo/gba.png", "color"=>"#2ECC71"],
 "gb"=>["label"=>"Game Boy","short"=>"GB","logo"=>"logo/gb.png", "color"=>"#F1C40F"],
 "gbc"=>["label"=>"Game Boy Color","short"=>"GBC","logo"=>"logo/gbc.png", "color"=>"#E67E22"],
 "psx"=>["label"=>"PlayStation","short"=>"PS1","logo"=>"logo/psx.png", "color"=>"#E74C3C"],
 "megadrive"=>["label"=>"Mega Drive","short"=>"MD","logo"=>"logo/megadrive.png", "color"=>"#3498DB"]
];

$currentSystem = $_GET['system'] ?? null;
$showFavorites = isset($_GET['favorites']);
$showAccount   = isset($_GET['account']);

/* =========================
   FAVORITOS
========================= */
$userFavorites=[];
if($logged){
    $uid=$_SESSION['user_id'];
    $res=$conn->query("SELECT system,rom FROM favorites WHERE user_id=$uid");
    while($r=$res->fetch_assoc()){
        $userFavorites[]=$r['system']."::".$r['rom'];
    }
}

/* =========================
   ROMS
========================= */
$roms=[];
if($currentSystem && isset($systems[$currentSystem])){
    $dir=__DIR__."/roms/$currentSystem";
    if(is_dir($dir)){
        foreach(scandir($dir) as $f){
            if(preg_match('/\.(zip|iso|bin)$/i',$f)) $roms[]=$f;
        }
        sort($roms);
    }
}

function cleanName($f){
    return trim(preg_replace('/\s*[\(\[].*?[\)\]]/','',pathinfo($f,PATHINFO_FILENAME)));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>System Beware Retro - CRT Edition</title>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">
<style>
/* ===== RESET Y VARIABLES ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --bg-primary: #0B0F1C;
    --bg-secondary: #151E2F;
    --accent-orange: #FF6B4A;
    --accent-cyan: #4AF7FF;
    --accent-amber: #F0E68C;
    --gold: #D4AF37;
    --silver: #A0A0A0;
    --text-primary: #E0E0E0;
    --text-dim: #8A9BB5;
    --border-glow: rgba(212, 175, 55, 0.3);
    --crt-shadow: 0 0 10px rgba(74, 247, 255, 0.3);
}

/* ===== CRT EFFECTS ===== */
body {
    margin: 0;
    background: #000;
    font-family: 'VT323', monospace;
    font-size: 18px;
    color: var(--text-primary);
    display: flex;
    position: relative;
    min-height: 100vh;
}

/* Scanlines */
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: repeating-linear-gradient(
        0deg,
        rgba(0, 0, 0, 0.15) 0px,
        rgba(0, 0, 0, 0.15) 1px,
        transparent 1px,
        transparent 2px
    );
    pointer-events: none;
    z-index: 999;
    animation: scanlines 8s linear infinite;
}

/* CRT flicker */
@keyframes flicker {
    0% { opacity: 1; }
    50% { opacity: 0.98; }
    100% { opacity: 1; }
}

/* Scanlines animation */
@keyframes scanlines {
    0% { background-position: 0 0; }
    100% { background-position: 0 10px; }
}

/* Glow effect */
.glow {
    text-shadow: 0 0 5px var(--accent-cyan), 0 0 10px var(--accent-cyan);
}

.gold-glow {
    text-shadow: 0 0 5px var(--gold), 0 0 10px var(--gold);
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 280px;
    background: var(--bg-secondary);
    padding: 24px 16px;
    transition: width 0.3s ease;
    border-right: 3px solid var(--gold);
    box-shadow: 5px 0 15px rgba(0,0,0,0.5), inset -2px 0 5px rgba(212, 175, 55, 0.2);
    position: relative;
    z-index: 10;
    display: flex;
    flex-direction: column;
    animation: flicker 3s infinite;
}

/* Sidebar decorative corners */
.sidebar::before,
.sidebar::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid var(--gold);
    pointer-events: none;
}

.sidebar::before {
    top: 10px;
    left: 10px;
    border-right: none;
    border-bottom: none;
}

.sidebar::after {
    bottom: 10px;
    right: 10px;
    border-left: none;
    border-top: none;
}

.sidebar.min {
    width: 100px;
}

.sidebar a {
    color: var(--text-primary);
    text-decoration: none;
    display: flex;
    gap: 16px;
    margin: 8px 0;
    align-items: center;
    padding: 10px 12px;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.sidebar a::before {
    content: "►";
    position: absolute;
    left: -20px;
    color: var(--accent-cyan);
    font-size: 14px;
    transition: left 0.2s ease;
}

.sidebar a:hover::before {
    left: 4px;
}

.sidebar a:hover {
    border-color: var(--accent-cyan);
    background: rgba(74, 247, 255, 0.1);
    box-shadow: 0 0 15px rgba(74, 247, 255, 0.3);
    transform: translateX(5px);
}

.sidebar a span.text {
    font-family: 'Press Start 2P', cursive;
    font-size: 12px;
    letter-spacing: 1px;
}

.sidebar.min span.text {
    display: none;
}

span.short {
    display: none;
    font-family: 'Press Start 2P', cursive;
    font-size: 12px;
}

.sidebar.min span.short {
    display: inline;
}

/* Toggle button */
#sidebarToggle {
    cursor: pointer;
    font-size: 24px;
    color: var(--gold);
    margin-bottom: 20px;
    text-align: right;
    transition: transform 0.3s ease;
}

#sidebarToggle:hover {
    transform: scale(1.2);
    color: var(--accent-cyan);
}

/* ===== ACCOUNT BOX ===== */
.account-box {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 16px 0 24px;
    padding: 12px;
    border: 2px solid var(--silver);
    background: rgba(0, 0, 0, 0.3);
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.account-box:hover {
    border-color: var(--gold);
    box-shadow: 0 0 20px var(--border-glow);
}

.account-box::after {
    content: "▼";
    position: absolute;
    right: 10px;
    color: var(--gold);
    font-size: 12px;
}

.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--gold);
    box-shadow: 0 0 15px var(--border-glow);
}

.sidebar.min .avatar {
    width: 40px;
    height: 40px;
}

.account-text {
    color: var(--text-primary);
    font-family: 'Press Start 2P', cursive;
    font-size: 10px;
    line-height: 1.4;
}

/* ===== MAIN CONTENT ===== */
.main {
    flex: 1;
    padding: 30px;
    background: var(--bg-primary);
    position: relative;
    z-index: 1;
    animation: flicker 3s infinite;
}

/* CRT corners for main */
.main::before,
.main::after {
    content: "";
    position: absolute;
    width: 30px;
    height: 30px;
    border: 3px solid var(--silver);
    opacity: 0.5;
    pointer-events: none;
    z-index: 5;
}

.main::before {
    top: 10px;
    left: 10px;
    border-right: none;
    border-bottom: none;
}

.main::after {
    bottom: 10px;
    right: 10px;
    border-left: none;
    border-top: none;
}

/* Page title */
h2 {
    font-family: 'Press Start 2P', cursive;
    font-size: 20px;
    color: var(--gold);
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 3px solid var(--accent-cyan);
    text-shadow: 3px 3px 0 rgba(74, 247, 255, 0.3);
    letter-spacing: 2px;
    position: relative;
}

h2::after {
    content: "";
    position: absolute;
    bottom: -6px;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--accent-orange);
}

/* ===== GRID DE JUEGOS ===== */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
    padding: 10px;
}

.game {
    background: var(--bg-secondary);
    padding: 15px;
    border-radius: 0;
    border: 2px solid var(--silver);
    text-align: center;
    position: relative;
    color: var(--text-primary);
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 5px 5px 0 rgba(0,0,0,0.5);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.game:hover {
    transform: translate(-3px, -3px);
    box-shadow: 8px 8px 0 rgba(74, 247, 255, 0.3);
    border-color: var(--accent-cyan);
}

.game::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--accent-cyan), var(--accent-orange));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.game:hover::before {
    opacity: 1;
}

.game img {
    width: 100%;
    height: 160px;
    object-fit: contain;
    image-rendering: pixelated;
    border: 2px solid var(--silver);
    background: #000;
    padding: 5px;
}

.game div {
    font-family: 'VT323', monospace;
    font-size: 18px;
    color: var(--accent-amber);
    text-shadow: 0 0 5px var(--accent-amber);
    word-break: break-word;
}

/* Star favorito */
.star {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    color: var(--silver);
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10;
    text-shadow: 0 0 5px currentColor;
}

.star.active {
    color: var(--gold);
    transform: scale(1.2);
    filter: drop-shadow(0 0 5px var(--gold));
}

.star:hover {
    transform: scale(1.3);
    color: var(--accent-cyan);
}

/* ===== PÁGINA DE CUENTA ===== */
.avatar-large {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--gold);
    box-shadow: 0 0 30px var(--border-glow);
    image-rendering: pixelated;
}

.avatar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, 90px);
    gap: 15px;
    margin: 20px 0;
}

.avatar-option {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.3s ease;
    filter: grayscale(50%);
}

.avatar-option:hover {
    transform: scale(1.1);
    border-color: var(--accent-cyan);
    filter: grayscale(0%);
    box-shadow: 0 0 20px var(--accent-cyan);
}

/* Formularios retro */
input, button, select, textarea {
    background: var(--bg-secondary);
    border: 3px solid var(--silver);
    color: var(--text-primary);
    padding: 12px 16px;
    font-family: 'VT323', monospace;
    font-size: 18px;
    margin: 8px 0;
    width: 100%;
    max-width: 400px;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.5);
    transition: all 0.3s ease;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--accent-cyan);
    box-shadow: 0 0 20px rgba(74, 247, 255, 0.3);
}

button {
    background: var(--bg-secondary);
    border: 3px solid var(--gold);
    color: var(--gold);
    font-weight: bold;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
    max-width: 200px;
}

button:hover {
    background: var(--gold);
    color: var(--bg-primary);
    box-shadow: 0 0 30px var(--gold);
    transform: translateY(-2px);
}

button:active {
    transform: translateY(2px);
    box-shadow: 0 0 10px var(--gold);
}

.logout-btn {
    background: transparent;
    border-color: var(--accent-orange);
    color: var(--accent-orange);
    max-width: 100%;
}

.logout-btn:hover {
    background: var(--accent-orange);
    color: var(--bg-primary);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 1000;
        transform: translateX(0);
    }
    
    .sidebar.min {
        transform: translateX(-180px);
    }
    
    .sidebar.min:hover {
        transform: translateX(0);
    }
    
    .main {
        margin-left: 280px;
        width: calc(100% - 280px);
    }
    
    .sidebar.min + .main {
        margin-left: 100px;
        width: calc(100% - 100px);
    }
    
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
}

@media (max-width: 480px) {
    .grid {
        grid-template-columns: 1fr;
    }
    
    h2 {
        font-size: 16px;
    }
    
    .avatar-large {
        width: 120px;
        height: 120px;
    }
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
    <div id="sidebarToggle" onclick="toggleSidebar()">☰</div>

    <!-- CUENTA -->
    <a href="?account=1">
        <div class="account-box">
            <img src="<?= $avatarPath ?>" class="avatar" alt="Avatar">
            <span class="account-text text"><?= $logged ? $username : 'Invitado' ?></span>
        </div>
    </a>

    <a href="index.php">
        <span class="text">INICIO</span>
        <span class="short">🏠</span>
    </a>

    <a href="?favorites=1">
        <span class="text">FAVORITOS</span>
        <span class="short">⭐</span>
    </a>

    <hr style="border: 1px solid var(--silver); margin: 15px 0;">

    <?php foreach($systems as $k=>$s): ?>
    <a href="?system=<?=$k?>" style="border-left: 4px solid <?=$s['color']?>;">
        <span class="text"><?=$s['label']?></span>
        <span class="short"><?=$s['short']?></span>
    </a>
    <?php endforeach ?>
</div>

<div class="main">

<?php if($showAccount && $logged): ?>
    <h2>⚡ CUENTA ⚡</h2>

    <div style="display: flex; align-items: center; gap: 30px; margin-bottom: 40px; flex-wrap: wrap;">
        <img src="<?= $avatarPath ?>" class="avatar-large" alt="Avatar">
        <div>
            <h3 style="font-family: 'Press Start 2P', cursive; color: var(--accent-cyan); margin-bottom: 10px;">
                <?=$username?>
            </h3>
            <p style="color: var(--text-dim);">Miembro desde: <?= date('Y-m-d') ?></p>
        </div>
    </div>

    <h3 style="color: var(--gold); margin: 30px 0 20px;">🎨 CAMBIAR AVATAR</h3>
    <div class="avatar-grid">
    <?php 
    $avatars = ['avatar1', 'avatar2', 'avatar3', 'avatar4', 'avatar5', 'avatar6', 'avatar7', 'avatar8', 'avatar9', 'avatar10', 'avatar11', 'avatar12'];
    foreach($avatars as $avatarName): ?>
        <img src="assets/avatars/<?=$avatarName?>.jpg"
             class="avatar-option <?= $avatar === $avatarName ? 'selected' : '' ?>"
             onclick="updateAvatar('<?=$avatarName?>')"
             title="<?= $avatarName ?>">
    <?php endforeach ?>
    </div>

    <h3 style="color: var(--gold); margin: 30px 0 20px;">✏️ CAMBIAR NOMBRE</h3>
    <input id="newUser" placeholder="Nuevo nombre de usuario" maxlength="20">
    <button onclick="updateUsername()" style="max-width: 200px;">GUARDAR</button>

    <button class="logout-btn" onclick="logout()" style="margin-top: 40px;">🚪 CERRAR SESIÓN</button>

<?php elseif($showAccount && !$logged): ?>
    <h2>⚡ CUENTA ⚡</h2>
    <p style="margin-bottom: 30px; color: var(--text-dim); font-size: 20px;">
        Inicia sesión o crea una cuenta para guardar tus favoritos.
    </p>

    <div style="max-width: 400px; border: 3px solid var(--silver); padding: 30px; box-shadow: inset 0 0 20px rgba(0,0,0,0.5);">
        <h3 style="color: var(--accent-cyan); margin-bottom: 20px;">🔐 INICIAR SESIÓN</h3>
        <input id="loginUser" placeholder="USUARIO">
        <input id="loginPass" placeholder="CONTRASEÑA" type="password">
        <button onclick="login()" style="background: var(--accent-cyan); border-color: var(--accent-cyan); color: var(--bg-primary);">ENTRAR</button>

        <p style="text-align: center; margin: 30px 0; color: var(--gold);">───── 〇 ─────</p>

        <h3 style="color: var(--accent-orange); margin-bottom: 20px;">✨ CREAR CUENTA</h3>
        <input id="regUser" placeholder="USUARIO">
        <input id="regPass" placeholder="CONTRASEÑA (mín. 8)" type="password">
        <button onclick="register()" style="background: var(--accent-orange); border-color: var(--accent-orange); color: var(--bg-primary);">REGISTRARSE</button>
    </div>

<?php elseif($showFavorites): ?>
    <h2>⭐ FAVORITOS ⭐</h2>
    <?php if(empty($userFavorites)): ?>
        <div style="text-align: center; padding: 50px; border: 3px dashed var(--silver);">
            <p style="font-size: 24px; color: var(--text-dim);">No tienes favoritos aún</p>
            <p style="margin-top: 20px;">¡Explora los sistemas y marca tus juegos con la estrella!</p>
        </div>
    <?php else: ?>
    <div class="grid">
    <?php foreach($userFavorites as $f):
        [$s,$r]=explode("::",$f);
        $caratula = getCaratulaPath($s, $r);
    ?>
        <a class="game" href="play.php?system=<?=$s?>&rom=<?=urlencode($r)?>">
            <span class="star active" onclick="toggleFav(event,'<?=$s?>','<?=$r?>')">★</span>
            <img src="<?=$caratula?>" alt="<?=cleanName($r)?>">
            <div><?=cleanName($r)?></div>
        </a>
    <?php endforeach ?>
    </div>
    <?php endif; ?>

<?php elseif($currentSystem): ?>
    <h2 style="border-left: 10px solid <?=$systems[$currentSystem]['color']?>; padding-left: 20px;">
        <?=$systems[$currentSystem]['label']?>
    </h2>
    <div class="grid">
    <?php foreach($roms as $r):
        $id=$currentSystem."::".$r;
        $caratula = getCaratulaPath($currentSystem, $r);
    ?>
        <a class="game" href="play.php?system=<?=$currentSystem?>&rom=<?=urlencode($r)?>">
            <span class="star <?=in_array($id,$userFavorites)?'active':''?>"
                  onclick="toggleFav(event,'<?=$currentSystem?>','<?=$r?>')">★</span>
            <img src="<?=$caratula?>" alt="<?=cleanName($r)?>">
            <div><?=cleanName($r)?></div>
        </a>
    <?php endforeach ?>
    </div>

<?php else: ?>
    <h2>🎮 SELECCIONA UNA CONSOLA 🎮</h2>
    <div class="grid">
    <?php foreach($systems as $k=>$s): ?>
        <a class="game" href="?system=<?=$k?>" style="border-color: <?=$s['color']?>;">
            <img src="<?=$s['logo']?>" alt="<?=$s['label']?>">
            <div><?=$s['label']?></div>
            <div style="font-size: 14px; color: var(--text-dim);">▶ PRESIONA ◀</div>
        </a>
    <?php endforeach ?>
    </div>
<?php endif ?>

</div>

<script>
const logged = <?= $logged ? 'true' : 'false' ?>;

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('min');
    localStorage.setItem('sidebar-min', document.getElementById('sidebar').classList.contains('min'));
}

// Recordar estado del sidebar
if (localStorage.getItem('sidebar-min') === 'true') {
    document.getElementById('sidebar').classList.add('min');
}

function toggleFav(e, s, r) {
    e.preventDefault();
    e.stopPropagation();
    if (!logged) {
        alert('🔐 Inicia sesión para guardar favoritos');
        window.location.href = '?account=1';
        return;
    }
    fetch('favorites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `system=${s}&rom=${encodeURIComponent(r)}`
    }).then(() => location.reload());
}

function updateAvatar(a) {
    fetch('auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_profile&avatar=${a}`
    }).then(() => location.reload());
}

function updateUsername() {
    const newName = document.getElementById('newUser').value.trim();
    if (!newName) {
        alert('❌ Escribe un nombre');
        return;
    }
    fetch('auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_username&username=${encodeURIComponent(newName)}`
    }).then(r => r.text()).then(res => {
        if (res === 'OK') {
            location.reload();
        } else {
            alert('❌ Error al actualizar');
        }
    });
}

function logout() {
    fetch('auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=logout'
    }).then(() => location.href = 'index.php');
}

function login() {
    const user = document.getElementById('loginUser').value.trim();
    const pass = document.getElementById('loginPass').value;

    if (!user || !pass) {
        alert('❌ Completa todos los campos');
        return;
    }

    fetch('auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=login&username=${encodeURIComponent(user)}&password=${encodeURIComponent(pass)}`
    }).then(r => r.text()).then(res => {
        if (res === 'OK') {
            location.href = '?account=1';
        } else {
            alert('❌ Usuario o contraseña incorrectos');
        }
    });
}

function register() {
    const user = document.getElementById('regUser').value.trim();
    const pass = document.getElementById('regPass').value;

    if (!user || !pass) {
        alert('❌ Completa todos los campos');
        return;
    }

    if (pass.length < 8) {
        alert('❌ La contraseña debe tener mínimo 8 caracteres');
        return;
    }

    fetch('auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=register&username=${encodeURIComponent(user)}&password=${encodeURIComponent(pass)}`
    }).then(r => r.text()).then(res => {
        if (res === 'OK') {
            alert('✅ Cuenta creada. Iniciando sesión...');
            login();
        } else if (res === 'EXISTS') {
            alert('❌ El usuario ya existe');
        } else if (res === 'SHORT') {
            alert('❌ La contraseña es muy corta');
        } else {
            alert('❌ Error al crear la cuenta');
        }
    });
}
</script>

<!-- GAMEPAD HANDLER Y NAVEGACIÓN (sin cambios) -->
<script src="data/src/gamepad.js"></script>
<script src="assets/gamepad-nav.js"></script>

</body>
</html>