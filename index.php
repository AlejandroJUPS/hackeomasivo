<?php
session_start();

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

$currentSystem=$_GET['system']??null;
$showFavorites=isset($_GET['favorites']);

/* =========================
   ROMS
========================= */
$roms=[];
if($currentSystem && isset($systems[$currentSystem])){
    $dir=__DIR__."/roms/$currentSystem";
    if(is_dir($dir)){
        foreach(scandir($dir) as $f){
            if(preg_match('/\.(zip|iso|bin)$/i',$f)){
                $roms[]=$f;
            }
        }
        sort($roms);
    }
}

/* =========================
   FAVORITOS (solo visual)
========================= */
$userFavorites=[]; // vacío por ahora

function cleanName($f){
    return trim(
        preg_replace(
            '/\s*[\(\[].*?[\)\]]/',
            '',
            pathinfo($f,PATHINFO_FILENAME)
        )
    );
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

.game{
 background:#111;
 padding:10px;
 border-radius:6px;
 text-align:center;
 position:relative;
 color:#fff;
 text-decoration:none
}
.game img{
 width:100%;
 height:140px;
 object-fit:contain;
 background:#000
}
.star{
 position:absolute;
 top:8px;
 right:8px;
 font-size:18px;
 color:#555;
 cursor:pointer
}
.star.active{color:#ffd700}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
<div onclick="toggleSidebar()" style="cursor:pointer;font-size:22px">☰</div>

<a href="index.php">
<svg class="icon" viewBox="0 0 24 24">
<path d="M3 10.5L12 3l9 7.5"/>
<path d="M5 10v10h5v-6h4v6h5V10"/>
</svg>
<span class="text">Inicio</span>
</a>

<a href="?favorites=1">
<svg class="icon" viewBox="0 0 24 24">
<path d="M12 3l3.1 6.3L22 10l-5 4.9L18.2 22 12 18.6 5.8 22 7 14.9 2 10l6.9-.7z"/>
</svg>
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
<p style="opacity:.6">Inicia sesión para usar favoritos</p>

<?php elseif($currentSystem): ?>
<h2><?=$systems[$currentSystem]['label']?></h2>
<div class="grid">
<?php foreach($roms as $r): ?>
<a class="game" href="play.php?system=<?=$currentSystem?>&rom=<?=urlencode($r)?>">
<span class="star">★</span>
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
function toggleSidebar(){
 document.getElementById("sidebar").classList.toggle("min");
}
</script>

</body>
</html>
