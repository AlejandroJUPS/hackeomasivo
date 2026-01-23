<?php
session_start();
require_once __DIR__ . "/db.php";

/* =========================
   SISTEMAS
========================= */
$systems = [
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

/* =========================
   FAVORITOS DEL USUARIO
========================= */
$userFavorites = [];

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $res = $conn->query("SELECT system, rom FROM favorites WHERE user_id=$uid");
    while ($r = $res->fetch_assoc()) {
        $userFavorites[] = $r['system']."::".$r['rom'];
    }
}

/* =========================
   ROMS
========================= */
$roms = [];

if ($currentSystem && isset($systems[$currentSystem])) {
    $dir = __DIR__ . "/roms/$currentSystem";
    if (is_dir($dir)) {
        foreach (scandir($dir) as $f) {
            if (preg_match('/\.(zip|iso|bin)$/i', $f)) {
                $roms[] = $f;
            }
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
.sidebar{width:240px;background:#0a0a0a;padding:16px;transition:.25s}
.sidebar.min{width:80px}
.sidebar a{color:#bbb;text-decoration:none;display:flex;gap:12px;margin:10px 0}
.sidebar a:hover{color:#fff}
.sidebar.min span.text{display:none}
span.short{display:none}
.sidebar.min span.short{display:inline}

.main{flex:1;padding:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:15px}

.game{
 background:#111;padding:10px;border-radius:6px;
 text-align:center;position:relative;
 color:#fff;text-decoration:none
}
.game img{
 width:100%;height:140px;
 object-fit:contain;background:#000
}

.star{
 position:absolute;top:8px;right:8px;
 font-size:18px;color:#777;cursor:pointer;
 transition:.15s
}
.star.active{color:#ffd700;transform:scale(1.2)}
.star:hover{transform:scale(1.3)}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
<div onclick="toggleSidebar()" style="cursor:pointer;font-size:22px">☰</div>

<a href="index.php">Inicio</a>
<a href="?favorites=1">Favoritos</a>
<hr>

<?php foreach($systems as $k=>$s): ?>
<a href="?system=<?=$k?>">
<span class="text"><?=$s['label']?></span>
<span class="short"><?=$s['short']?></span>
</a>
<?php endforeach ?>

<hr>

<?php if(isset($_SESSION['user_id'])): ?>
<div>
👤 <?=htmlspecialchars($_SESSION['username'])?><br>
<a href="#" onclick="logout()">Cerrar sesión</a>
</div>
<?php else: ?>
<a href="#" onclick="showLogin()">Login / Registro</a>
<?php endif ?>
</div>

<!-- MAIN -->
<div class="main">

<?php if($showFavorites): ?>

<h2>⭐ Favoritos</h2>

<?php if(!isset($_SESSION['user_id'])): ?>
<p>Debes iniciar sesión.</p>
<?php else: ?>

<div class="grid">
<?php foreach($userFavorites as $f):
[$s,$r]=explode("::",$f); ?>
<a class="game" href="play.php?system=<?=$s?>&rom=<?=urlencode($r)?>">
<span class="star active"
 onclick="toggleFav(event,'<?=$s?>','<?=$r?>',this)">★</span>
<img src="<?=$systems[$s]['logo']?>">
<div><?=cleanName($r)?></div>
</a>
<?php endforeach ?>
</div>

<?php endif ?>

<?php elseif($currentSystem): ?>

<h2><?=$systems[$currentSystem]['label']?></h2>

<div class="grid">
<?php foreach($roms as $r):
$id=$currentSystem."::".$r; ?>
<a class="game" href="play.php?system=<?=$currentSystem?>&rom=<?=urlencode($r)?>">
<span class="star <?=in_array($id,$userFavorites)?'active':''?>"
 onclick="toggleFav(event,'<?=$currentSystem?>','<?=$r?>',this)">★</span>
<img src="<?=$systems[$currentSystem]['logo']?>">
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

<!-- LOGIN MODAL -->
<div class="modal" id="loginModal"
 style="position:fixed;inset:0;background:rgba(0,0,0,.7);
 display:none;align-items:center;justify-content:center">
<div style="background:#111;padding:20px;border-radius:8px;width:280px">
<h3>Login / Registro</h3>
<input id="lu" placeholder="Usuario" style="width:100%;padding:8px">
<input id="lp" type="password" placeholder="Contraseña"
 style="width:100%;padding:8px;margin-top:6px">
<button onclick="login()">Login</button>
<button onclick="register()">Registrar</button>
</div>
</div>

<script>
const logged = <?=isset($_SESSION['user_id'])?'true':'false'?>;

function toggleSidebar(){
 document.getElementById("sidebar").classList.toggle("min");
}

function showLogin(){
 document.getElementById("loginModal").style.display="flex";
}

function login(){
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`action=login&username=${lu.value}&password=${lp.value}`
 }).then(r=>r.text()).then(t=>{
  if(t==="OK") location.reload();
  else alert("Login incorrecto");
 });
}

function register(){
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`action=register&username=${lu.value}&password=${lp.value}`
 }).then(r=>r.text()).then(t=>{
  if(t==="OK") alert("Usuario creado");
  else alert(t);
 });
}

function logout(){
 fetch("auth.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:"action=logout"
 }).then(()=>location.reload());
}

function toggleFav(e,s,r,el){
 e.preventDefault();
 e.stopPropagation();

 if(!logged){ showLogin(); return; }

 fetch("favorites.php",{
  method:"POST",
  headers:{'Content-Type':'application/x-www-form-urlencoded'},
  body:`system=${s}&rom=${encodeURIComponent(r)}`
 })
 .then(r=>r.text())
 .then(t=>{
   if(t==="REMOVED"){
     const game = el.closest(".game");
     if(game) game.remove();
   }
 });
}
</script>

</body>
</html>
