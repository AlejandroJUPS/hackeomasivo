<?php
session_start();
require_once "db.php";

/* =========================
   USUARIO / AVATAR
========================= */

$loggedIn = isset($_SESSION['user_id']);
$userAvatar = "uploads/avatars/default.png";

if ($loggedIn) {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!empty($res['avatar']) && file_exists("uploads/avatars/".$res['avatar'])) {
        $userAvatar = "uploads/avatars/".$res['avatar'];
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>System Beware Retro</title>

<style>
body{
    margin:0;
    background:#000;
    color:#fff;
    font-family:Arial;
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:240px;
    background:#0a0a0a;
    padding:16px;
    transition:.25s;
}
.sidebar.min{width:80px}
.sidebar a{
    color:#bbb;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:12px;
    margin:12px 0;
}
.sidebar a:hover{color:#fff}
.sidebar.min span.text{display:none}

/* AVATAR */
.avatar-box{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:20px;
    cursor:pointer;
}
.avatar-box img{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #e53935;
}

/* MAIN */
.main{flex:1;padding:20px}

/* CUENTA */
.account-box{
    max-width:420px;
    background:#111;
    padding:20px;
    border-radius:8px;
}
.account-box img{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    display:block;
    margin:0 auto 15px;
}
.account-box input{
    width:100%;
    padding:8px;
    margin-top:10px;
}
.account-box button{
    width:100%;
    margin-top:10px;
    padding:10px;
    background:#e53935;
    border:none;
    color:#fff;
    cursor:pointer;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <!-- AVATAR / CUENTA -->
    <a class="avatar-box" href="?account=1">
        <img src="<?=$userAvatar?>">
        <span class="text">Cuenta</span>
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
        </a>
    <?php endforeach ?>

</div>

<!-- MAIN -->
<div class="main">

<?php if ($showAccount): ?>

    <h2>👤 Cuenta</h2>

    <div class="account-box">
        <img src="<?=$userAvatar?>">

        <?php if ($loggedIn): ?>
            <form action="auth.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_avatar">
                <input type="file" name="avatar" accept="image/*" required>
                <button>Cambiar avatar</button>
            </form>
        <?php else: ?>
            <p>Inicia sesión para personalizar tu perfil.</p>
        <?php endif ?>
    </div>

<?php elseif ($showFavorites): ?>

    <h2>⭐ Favoritos</h2>
    <!-- aquí ya entra tu sistema de favoritos -->

<?php elseif ($currentSystem): ?>

    <h2><?=$systems[$currentSystem]['label']?></h2>
    <!-- grid de juegos -->

<?php else: ?>

    <h2>Selecciona una consola</h2>

<?php endif ?>

</div>

</body>
</html>
