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
 "nes"=>["label"=>"NES","short"=>"NES","logo"=>"logo/nes.png"],
 "snes"=>["label"=>"SNES","short"=>"SNES","logo"=>"logo/snes.png"],
 "n64"=>["label"=>"Nintendo 64","short"=>"N64","logo"=>"logo/n64.png"],
 "gba"=>["label"=>"Game Boy Advance","short"=>"GBA","logo"=>"logo/gba.png"],
 "gb"=>["label"=>"Game Boy","short"=>"GB","logo"=>"logo/gb.png"],
 "gbc"=>["label"=>"Game Boy Color","short"=>"GBC","logo"=>"logo/gbc.png"],
 "psx"=>["label"=>"PlayStation","short"=>"PS1","logo"=>"logo/psx.png"],
 "megadrive"=>["label"=>"Mega Drive","short"=>"MD","logo"=>"logo/megadrive.png"]
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
<title>System Beware Retro</title>

<style>
body{margin:0;background:#000;color:#fff;font-family:Arial;display:flex}

/* SIDEBAR */
.sidebar{width:240px;background:#0a0a0a;padding:16px;transition:.25s}
.sidebar.min{width:80px}
.sidebar a{color:#bbb;text-decoration:none;display:flex;gap:12px;margin:10px 0;align-items:center}
.sidebar a:hover{color:#fff}
.sidebar.min span.text{display:none}
span.short{display:none}
.sidebar.min span.short{display:inline}

.account-box{
 display:flex;
 align-items:center;
 gap:12px;
 margin:12px 0 18px;
 cursor:pointer;
}
.avatar{
 width:42px;
 height:42px;
 border-radius:50%;
 object-fit:cover;
 background:#111;
}
.sidebar.min .avatar{
 width:36px;
 height:36px;
}
.account-text{
 color:#fff;
}

/* MAIN */
.main{flex:1;padding:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:15px}

.game{background:#111;padding:10px;border-radius:6px;text-align:center;position:relative;color:#fff;text-decoration:none}
.game img{width:100%;height:140px;object-fit:contain;background:#000}
.star{position:absolute;top:8px;right:8px;font-size:18px;color:#777;cursor:pointer;transition:.2s}
.star.active{color:#ffd700;transform:scale(1.2)}
.star:hover{transform:scale(1.3)}

/* ACCOUNT */
.avatar-large{
 width:140px;
 height:140px;
 border-radius:50%;
 object-fit:cover;
 border:3px solid #e53935;
 margin-bottom:20px;
}
.avatar-grid{
 display:grid;
 grid-template-columns:repeat(auto-fill,80px);
 gap:12px;
}
.avatar-option{
 width:72px;
 height:72px;
 border-radius:50%;
 cursor:pointer;
 border:2px solid transparent;
}
.avatar-option:hover{
 border-color:#ffd700;
}

/* LOGOUT BUTTON */
.logout-btn{
 background:#e53935;
 color:#fff;
 border:none;
 padding:10px 16px;
 border-radius:4px;
 cursor:pointer;
 margin-top:20px;
 width:100%;
 font-size:14px;
 transition:.2s;
}
.logout-btn:hover{
 background:#c62828;
}

/* GAMEPAD NAVIGATION */
.gamepad-focused {
 outline: 3px solid #ffd700 !important;
 outline-offset: 2px;
 transform: scale(1.05);
 transition: all 0.1s ease;
}
.gamepad-focused.game {
 outline-color: #ff9800;
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
<div onclick="toggleSidebar()" style="cursor:pointer;font-size:22px">☰</div>

<!-- CUENTA -->
<a href="?account=1">
 <div class="account-box">
   <img src="<?= $avatarPath ?>" class="avatar">
   <span class="account-text text">Cuenta</span>
 </div>
</a>

<a href="index.php">
 <span class="text">Inicio</span>
</a>

<a href="?favorites=1">
 <span class="text">Favoritos</span>
</a>

<hr>
<?php foreach($systems as $k=>$s): ?>
<a href="?system=<?=$k?>">
 <span class="text"><?=$s['label']?></span>
 <span class="short"><?=$s['short']?></span>
</a>
<?php endforeach ?>
</div>

<div class="main">

<?php if($showAccount && $logged): ?>
<h2>Cuenta</h2>

<div style="display:flex;align-items:center;gap:20px;margin-bottom:30px">
 <img src="<?= $avatarPath ?>" class="avatar-large" style="margin-bottom:0">
 <div>
  <h3 style="margin:0"><?=$username?></h3>
 </div>
</div>

<h3>Cambiar avatar</h3>
<div class="avatar-grid">
<?php 
$avatars = ['avatar1', 'avatar2', 'avatar3', 'avatar4', 'avatar5', 'avatar6', 'avatar7', 'avatar8', 'avatar9', 'avatar10', 'avatar11', 'avatar12'];
foreach($avatars as $avatarName): ?>
<img src="assets/avatars/<?=$avatarName?>.jpg"
 class="avatar-option"
 onclick="updateAvatar('<?=$avatarName?>')">
<?php endforeach ?>
</div>

<h3>Cambiar nombre</h3>
<input id="newUser" placeholder="Nuevo nombre">
<button onclick="updateUsername()">Guardar</button>

<button class="logout-btn" onclick="logout()">Cerrar sesión</button>

<?php elseif($showAccount && !$logged): ?>
<h2>Cuenta</h2>
<p style="margin-bottom:30px;">Inicia sesión o crea una cuenta para guardar tus favoritos.</p>

<div style="max-width:400px">
<h3>Iniciar sesión</h3>
<input id="loginUser" placeholder="Usuario" style="width:100%;padding:10px;margin:10px 0;border-radius:4px;border:1px solid #555;background:#1a1a1a;color:#fff">
<input id="loginPass" placeholder="Contraseña" type="password" style="width:100%;padding:10px;margin:10px 0;border-radius:4px;border:1px solid #555;background:#1a1a1a;color:#fff">
<button onclick="login()" style="width:100%;padding:10px;margin:10px 0;background:#2196F3;color:#fff;border:none;border-radius:4px;cursor:pointer;transition:.2s">Inicia sesión</button>
<p style="text-align:center;margin:20px 0">o</p>

<h3>Crear cuenta</h3>
<input id="regUser" placeholder="Usuario" style="width:100%;padding:10px;margin:10px 0;border-radius:4px;border:1px solid #555;background:#1a1a1a;color:#fff">
<input id="regPass" placeholder="Contraseña (mín. 8 caracteres)" type="password" style="width:100%;padding:10px;margin:10px 0;border-radius:4px;border:1px solid #555;background:#1a1a1a;color:#fff">
<button onclick="register()" style="width:100%;padding:10px;margin:10px 0;background:#4CAF50;color:#fff;border:none;border-radius:4px;cursor:pointer;transition:.2s">Crear cuenta</button>
</div>

<?php elseif($showFavorites): ?>
<h2>⭐ Favoritos</h2>
<div class="grid">
<?php foreach($userFavorites as $f):
 [$s,$r]=explode("::",$f);
 $caratula = getCaratulaPath($s, $r);
?>
<a class="game" href="play.php?system=<?=$s?>&rom=<?=urlencode($r)?>">
<span class="star active" onclick="toggleFav(event,'<?=$s?>','<?=$r?>')">★</span>
<img src="<?=$caratula?>">
<div><?=cleanName($r)?></div>
</a>
<?php endforeach ?>
</div>

<?php elseif($currentSystem): ?>
<h2><?=$systems[$currentSystem]['label']?></h2>
<div class="grid">
<?php foreach($roms as $r):
$id=$currentSystem."::".$r;
$caratula = getCaratulaPath($currentSystem, $r);
?>
<a class="game" href="play.php?system=<?=$currentSystem?>&rom=<?=urlencode($r)?>">
<span class="star <?=in_array($id,$userFavorites)?'active':''?>"
onclick="toggleFav(event,'<?=$currentSystem?>','<?=$r?>')">★</span>
<img src="<?=$caratula?>">
<div><?=cleanName($r)?></div>
</a>
<?php endforeach ?>
</div>

<?php else: ?>
<h2>Selecciona una consola</h2>
<div class="grid">
<?php foreach($systems as $k=>$s): ?>
<a class="game" href="?system=<?=$k?>">
<img src="<?=$s['logo']?>">
<div><?=$s['label']?></div>
</a>
<?php endforeach ?>
</div>
<?php endif ?>

</div>

<script>
const logged = <?= $logged?'true':'false' ?>;

function toggleSidebar(){
 document.getElementById("sidebar").classList.toggle("min");
}

function toggleFav(e,s,r){
 e.preventDefault();e.stopPropagation();
 if(!logged){alert("Inicia sesión");return;}
 fetch("favorites.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`system=${s}&rom=${encodeURIComponent(r)}`
 }).then(()=>location.reload());
}

function updateAvatar(a){
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`action=update_profile&avatar=${a}`
 }).then(()=>location.reload());
}

function updateUsername(){
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`action=update_username&username=${newUser.value}`
 }).then(()=>location.reload());
}

function logout(){
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`action=logout`
 }).then(()=>location.href='index.php');
}

function login(){
 const user = document.getElementById('loginUser').value.trim();
 const pass = document.getElementById('loginPass').value;
 
 if(!user || !pass){
  alert('Completa todos los campos');
  return;
 }
 
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`action=login&username=${encodeURIComponent(user)}&password=${encodeURIComponent(pass)}`
 }).then(r=>r.text()).then(res=>{
  if(res==='OK'){
   location.href='?account=1';
  }else{
   alert('Usuario o contraseña incorrectos');
  }
 });
}

function register(){
 const user = document.getElementById('regUser').value.trim();
 const pass = document.getElementById('regPass').value;
 
 if(!user || !pass){
  alert('Completa todos los campos');
  return;
 }
 
 if(pass.length < 8){
  alert('La contraseña debe tener mínimo 8 caracteres');
  return;
 }
 
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`action=register&username=${encodeURIComponent(user)}&password=${encodeURIComponent(pass)}`
 }).then(r=>r.text()).then(res=>{
  if(res==='OK'){
   alert('Cuenta creada. Iniciando sesión...');
   login();
  }else if(res==='EXISTS'){
   alert('El usuario ya existe');
  }else if(res==='SHORT'){
   alert('La contraseña es muy corta');
  }else{
   alert('Error al crear la cuenta');
  }
 });
}
</script>

<!-- GAMEPAD HANDLER -->
<script src="data/src/gamepad.js"></script>
<!-- GAMEPAD NAVIGATION -->
<script src="assets/gamepad-nav.js"></script>

</body>
</html>
