<?php
session_start();
require_once "db.php";

/* =========================
   FAVORITOS DEL USUARIO
========================= */
$userFavorites = [];
if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $res = $conn->prepare("SELECT system,rom FROM favorites WHERE user_id=?");
    $res->bind_param("i",$uid);
    $res->execute();
    $r = $res->get_result();
    while($row=$r->fetch_assoc()){
        $userFavorites[] = $row['system']."::".$row['rom'];
    }
}

/* =========================
   SISTEMAS
========================= */
$systems = [
 "nes"=>["label"=>"NES","short"=>"NES","logo"=>"logos/nes.png"],
 "snes"=>["label"=>"SNES","short"=>"SNES","logo"=>"logos/snes.png"],
 "n64"=>["label"=>"Nintendo 64","short"=>"N64","logo"=>"logos/n64.png"],
 "gba"=>["label"=>"Game Boy Advance","short"=>"GBA","logo"=>"logos/gba.png"],
 "gb"=>["label"=>"Game Boy","short"=>"GB","logo"=>"logos/gb.png"],
 "gbc"=>["label"=>"Game Boy Color","short"=>"GBC","logo"=>"logos/gbc.png"],
 "psx"=>["label"=>"PlayStation","short"=>"PS1","logo"=>"logos/psx.png"],
 "megadrive"=>["label"=>"Mega Drive","short"=>"MD","logo"=>"logos/megadrive.png"]
];

$currentSystem = $_GET['system'] ?? null;
$showFavorites = isset($_GET['favorites']);
$showAccount   = isset($_GET['account']);

$roms=[];
if($currentSystem && isset($systems[$currentSystem])){
    $dir = __DIR__."/roms/$currentSystem";
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
.sidebar{width:240px;background:#0a0a0a;padding:16px;transition:.25s}
.sidebar.min{width:80px}
.sidebar a{color:#bbb;text-decoration:none;display:flex;gap:12px;margin:10px 0}
.sidebar a:hover{color:#fff}
.sidebar.min span.text{display:none}
span.short{display:none}
.sidebar.min span.short{display:inline}
.icon{width:28px;height:28px;stroke:#e53935;fill:none;stroke-width:2.5}

.main{flex:1;padding:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:15px}

.game{background:#111;padding:10px;border-radius:6px;text-align:center;position:relative;color:#fff;text-decoration:none}
.game img{width:100%;height:140px;object-fit:contain;background:#000}

.star{
 position:absolute;top:8px;right:8px;
 font-size:18px;color:#777;cursor:pointer;
 transition:.2s
}
.star:hover{transform:scale(1.3)}
.star.active{color:#ffd700;transform:scale(1.3)}

.account-panel{max-width:520px;margin:auto}
.avatar-grid{
 display:grid;
 grid-template-columns:repeat(auto-fill,minmax(90px,1fr));
 gap:16px;margin:15px 0
}
.avatar-option{
 width:90px;height:90px;
 background:#111;border-radius:10px;
 display:flex;align-items:center;justify-content:center;
 cursor:pointer;border:2px solid transparent
}
.avatar-option img{
 width:72px;height:72px;image-rendering:pixelated
}
.avatar-option.selected{border-color:#ffd700}

.color-grid{
 display:grid;grid-template-columns:repeat(6,1fr);
 gap:14px;margin:15px 0
}
.color-option{
 width:46px;height:46px;border-radius:50%;
 cursor:pointer;border:2px solid transparent
}
.color-option.selected{border-color:#fff}

.switch-link{
 color:#4da3ff;text-decoration:underline;
 cursor:pointer;font-size:14px;margin-top:10px;display:inline-block
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
<div onclick="sidebar.classList.toggle('min')" style="cursor:pointer">☰</div>

<a href="index.php"><span class="text">Inicio</span></a>
<a href="?favorites=1"><span class="text">Favoritos</span></a>
<a href="?account=1"><span class="text">Cuenta</span></a>
<hr>

<?php foreach($systems as $k=>$s): ?>
<a href="?system=<?=$k?>">
 <span class="text"><?=$s['label']?></span>
 <span class="short"><?=$s['short']?></span>
</a>
<?php endforeach ?>
</div>

<div class="main">

<?php if($showAccount): ?>
<div class="account-panel">

<?php if(!isset($_SESSION['user_id'])): ?>

<h2>Cuenta</h2>

<div id="loginBox">
<input id="lu" placeholder="Usuario">
<input id="lp" type="password" placeholder="Contraseña">
<button onclick="login()">Iniciar sesión</button>
<div class="switch-link" onclick="showRegister()">Registrarse</div>
</div>

<div id="registerBox" style="display:none">
<input id="ru" placeholder="Usuario">
<input id="rp" type="password" placeholder="Contraseña (8+)">

<h3>Elige un avatar</h3>
<div class="avatar-grid">
<?php for($i=1;$i<=10;$i++): ?>
<div class="avatar-option" onclick="selectAvatar('avatar<?=$i?>.png',this)">
<img src="assets/avatars/avatar<?=$i?>.png">
</div>
<?php endfor ?>
</div>

<div class="switch-link" onclick="showColors()">Omitir avatar y elegir color</div>

<div id="colorBox" style="display:none">
<div class="color-grid">
<?php foreach(["#e53935","#8e24aa","#3949ab","#039be5","#00897b","#43a047","#fdd835","#fb8c00"] as $c): ?>
<div class="color-option" style="background:<?=$c?>" onclick="selectColor('<?=$c?>',this)"></div>
<?php endforeach ?>
</div>
</div>

<button onclick="register()">Crear cuenta</button>
<div class="switch-link" onclick="showLogin()">Volver</div>
</div>

<?php else: ?>

<h2>Cuenta</h2>
<div style="text-align:center">
<?php if($_SESSION['avatar']): ?>
<img src="assets/avatars/<?=$_SESSION['avatar']?>" style="width:96px">
<?php else: ?>
<div style="width:96px;height:96px;border-radius:50%;background:<?=$_SESSION['color']?>;margin:auto"></div>
<?php endif ?>
<p><?=$_SESSION['username']?></p>
<button onclick="logout()">Cerrar sesión</button>
</div>

<?php endif ?>
</div>

<?php elseif($showFavorites): ?>

<h2>⭐ Favoritos</h2>
<div class="grid">
<?php foreach($userFavorites as $f):
[$s,$r]=explode("::",$f); ?>
<a class="game" href="play.php?system=<?=$s?>&rom=<?=urlencode($r)?>">
<span class="star active" onclick="toggleFav(event,'<?=$s?>','<?=$r?>')">★</span>
<img src="<?=$systems[$s]['logo']?>">
<div><?=cleanName($r)?></div>
</a>
<?php endforeach ?>
</div>

<?php elseif($currentSystem): ?>

<h2><?=$systems[$currentSystem]['label']?></h2>
<div class="grid">
<?php foreach($roms as $r):
$id=$currentSystem."::".$r; ?>
<a class="game" href="play.php?system=<?=$currentSystem?>&rom=<?=urlencode($r)?>">
<span class="star <?=in_array($id,$userFavorites)?'active':''?>"
onclick="toggleFav(event,'<?=$currentSystem?>','<?=$r?>')">★</span>
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

<script>
let selectedAvatar="", selectedColor="";

function showRegister(){loginBox.style.display="none";registerBox.style.display="block"}
function showLogin(){loginBox.style.display="block";registerBox.style.display="none";colorBox.style.display="none"}
function showColors(){colorBox.style.display="block"}

function selectAvatar(a,el){
 selectedAvatar=a;selectedColor="";
 document.querySelectorAll(".avatar-option").forEach(e=>e.classList.remove("selected"));
 el.classList.add("selected");
}

function selectColor(c,el){
 selectedColor=c;selectedAvatar="";
 document.querySelectorAll(".color-option").forEach(e=>e.classList.remove("selected"));
 el.classList.add("selected");
}

function login(){
 fetch("auth.php",{method:"POST",headers:{'Content-Type':'application/x-www-form-urlencoded'},
 body:`action=login&username=${lu.value}&password=${lp.value}`})
 .then(r=>r.text()).then(t=>t==="OK"?location.reload():alert("Error"));
}

function register(){
 fetch("auth.php",{method:"POST",headers:{'Content-Type':'application/x-www-form-urlencoded'},
 body:`action=register&username=${ru.value}&password=${rp.value}&avatar=${selectedAvatar}&color=${selectedColor}`})
 .then(r=>r.text()).then(t=>t==="OK"?location.reload():alert(t));
}

function toggleFav(e,s,r){
 e.preventDefault();e.stopPropagation();
 fetch("favorites.php",{method:"POST",headers:{'Content-Type':'application/x-www-form-urlencoded'},
 body:`system=${s}&rom=${encodeURIComponent(r)}`})
 .then(()=>location.reload());
}

function logout(){
 fetch("auth.php",{method:"POST",headers:{'Content-Type':'application/x-www-form-urlencoded'},
 body:`action=logout`}).then(()=>location.reload());
}
</script>

</body>
</html>
