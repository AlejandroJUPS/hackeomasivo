<?php
session_start();

/* =========================
   CONEXIÓN DB
========================= */
$DB_HOST = "sql103.infinityfree.com";
$DB_USER = "if0_40909083";
$DB_PASS = "l26odsVdk4PSgga";
$DB_NAME = "if0_40909083_emutable";

$conn = new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if($conn->connect_error){ die("DB ERROR"); }

/* =========================
   ACCIONES AJAX
========================= */
if(isset($_POST['action'])){

    /* LOGIN */
    if($_POST['action']==='login'){
        $u=trim($_POST['username']);
        $p=$_POST['password'];

        $st=$conn->prepare("SELECT id,password FROM users WHERE username=?");
        $st->bind_param("s",$u);
        $st->execute();
        $result=$st->get_result();
        $row=$result->fetch_assoc();

        if($row && password_verify($p,$row['password'])){
            $id=$row['id'];
            $_SESSION['user_id']=$id;
            $_SESSION['username']=$u;
            echo "OK";
        }else echo "ERROR";
        exit;
    }

    /* REGISTER */
    if($_POST['action']==='register'){
        $u=trim($_POST['username']);
        $p=$_POST['password'];
        if(strlen($p)<8){ echo "SHORT"; exit; }

        $hash=password_hash($p,PASSWORD_DEFAULT);
        $st=$conn->prepare("INSERT INTO users(username,password) VALUES(?,?)");
        $st->bind_param("ss",$u,$hash);
        echo $st->execute()?"OK":"EXISTS";
        exit;
    }

    /* TOGGLE FAVORITO */
    if($_POST['action']==='toggle_favorite'){
        if(!isset($_SESSION['user_id'])){ echo "NO_LOGIN"; exit; }

        $uid=$_SESSION['user_id'];
        $system=$_POST['system'];
        $rom=$_POST['rom'];

        $st=$conn->prepare(
            "SELECT id FROM favorites WHERE user_id=? AND system=? AND rom=?"
        );
        $st->bind_param("iss",$uid,$system,$rom);
        $st->execute(); $st->store_result();

        if($st->num_rows){
            $del=$conn->prepare(
                "DELETE FROM favorites WHERE user_id=? AND system=? AND rom=?"
            );
            $del->bind_param("iss",$uid,$system,$rom);
            $del->execute();
            echo "REMOVED";
        }else{
            $ins=$conn->prepare(
                "INSERT INTO favorites(user_id,system,rom) VALUES(?,?,?)"
            );
            $ins->bind_param("iss",$uid,$system,$rom);
            $ins->execute();
            echo "ADDED";
        }
        exit;
    }
}

/* =========================
   FAVORITOS (FETCH)
========================= */
$userFavorites=[];
if(isset($_SESSION['user_id'])){
    $uid=$_SESSION['user_id'];
    $res=$conn->query("SELECT system,rom FROM favorites WHERE user_id=$uid");
    while($r=$res->fetch_assoc()){
        $userFavorites[]=$r['system']."::".$r['rom'];
    }
}

/* =========================
   SISTEMAS
========================= */
$systems=[
 "nes"=>["label"=>"NES","short"=>"NES","logo"=>"logos/nes.png"],
 "snes"=>["label"=>"SNES","short"=>"SNES","logo"=>"logos/snes.png"],
 "n64"=>["label"=>"Nintendo 64","short"=>"N64","logo"=>"logos/n64.png"],
 "gba"=>["label"=>"Game Boy Advance","short"=>"GBA","logo"=>"logos/gba.png"],
 "gb"=>["label"=>"Game Boy","short"=>"GB","logo"=>"logos/gb.png"],
 "gbc"=>["label"=>"Game Boy Color","short"=>"GBC","logo"=>"logos/gbc.png"],
 "psx"=>["label"=>"PlayStation","short"=>"PS1","logo"=>"logos/psx.png"],
 "megadrive"=>["label"=>"Mega Drive","short"=>"MD","logo"=>"logos/megadrive.png"]
];

$currentSystem=$_GET['system']??null;
$showFavorites=isset($_GET['favorites']);

$roms=[];$popular=[];

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
.star{position:absolute;top:8px;right:8px;font-size:18px;color:#777;cursor:pointer}
.star.active{color:#ffd700;transform:scale(1.2)}
.star:hover{transform:scale(1.3)}

.modal{position:fixed;inset:0;background:rgba(0,0,0,.7);display:none;align-items:center;justify-content:center}
.modal-box{background:#111;padding:20px;border-radius:8px;width:280px}
.modal input{width:100%;padding:8px;margin:6px 0}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
<div onclick="toggleSidebar()" style="cursor:pointer;font-size:22px">☰</div>

<a href="index.php">
<svg class="icon" viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 10v10h5v-6h4v6h5V10"/></svg>
<span class="text">Inicio</span>
</a>

<a href="?favorites=1">
<svg class="icon" viewBox="0 0 24 24"><path d="M12 3l3.1 6.3L22 10l-5 4.9L18.2 22 12 18.6 5.8 22 7 14.9 2 10l6.9-.7z"/></svg>
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

<?php if($showFavorites): ?>
<h2>⭐ Favoritos</h2>
<div class="grid">
<?php foreach($userFavorites as $f):
    [$s,$r]=explode("::",$f); ?>
<a class="game" href="play.php?system=<?=$s?>&rom=<?=urlencode($r)?>">
<span class="star active" onclick="toggleFav(event,'<?=$s?>','<?=$r?>')">★</span>
<img src="logos/<?=$s?>.png">
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

<!-- LOGIN MODAL -->
<div class="modal" id="loginModal">
<div class="modal-box">
<h3>Login / Registro</h3>
<input id="lu" placeholder="Usuario">
<input id="lp" type="password" placeholder="Contraseña">
<button onclick="login()">Login</button>
<button onclick="register()">Registrar</button>
</div>
</div>

<script>
const logged=<?=isset($_SESSION['user_id'])?'true':'false'?>;

function toggleSidebar(){
 document.getElementById("sidebar").classList.toggle("min");
}

function toggleFav(e,s,r){
 e.preventDefault();e.stopPropagation();
 if(!logged){showLogin();return;}
 fetch("index.php",{method:"POST",
 headers:{'Content-Type':'application/x-www-form-urlencoded'},
 body:`action=toggle_favorite&system=${s}&rom=${encodeURIComponent(r)}`})
 .then(()=>location.reload());
}

function showLogin(){document.getElementById("loginModal").style.display="flex";}
function login(){
 fetch("index.php",{method:"POST",
 headers:{'Content-Type':'application/x-www-form-urlencoded'},
 body:`action=login&username=${lu.value}&password=${lp.value}`})
 .then(r=>r.text()).then(t=>t==="OK"?location.reload():alert("Error"));
}
function register(){
 fetch("index.php",{method:"POST",
 headers:{'Content-Type':'application/x-www-form-urlencoded'},
 body:`action=register&username=${lu.value}&password=${lp.value}`})
 .then(r=>r.text()).then(t=>t==="OK"?alert("Registrado"):alert("Error"));
}
</script>

</body>
</html>
