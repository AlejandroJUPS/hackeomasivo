<?php
session_start();
require_once "db.php";

/* =========================
   LOGIN
========================= */
if(isset($_POST['action']) && $_POST['action']==='login'){

    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if($u==='' || $p===''){
        echo "ERROR";
        exit;
    }

    $st = $conn->prepare(
        "SELECT id, password, avatar, color 
         FROM users 
         WHERE username=?"
    );
    $st->bind_param("s", $u);
    $st->execute();
    $res = $st->get_result();
    $row = $res->fetch_assoc();

    if($row && password_verify($p, $row['password'])){
        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $u;
        $_SESSION['avatar']   = $row['avatar'];
        $_SESSION['color']    = $row['color'];
        echo "OK";
    }else{
        echo "ERROR";
    }
    exit;
}

/* =========================
   REGISTER
========================= */
if(isset($_POST['action']) && $_POST['action']==='register'){

    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $avatar = $_POST['avatar'] ?? null;
    $color  = $_POST['color']  ?? null;

    if(strlen($u)<3 || strlen($p)<8){
        echo "INVALID";
        exit;
    }

    $hash = password_hash($p, PASSWORD_DEFAULT);

    $st = $conn->prepare(
        "INSERT INTO users(username,password,avatar,color)
         VALUES(?,?,?,?)"
    );
    $st->bind_param("ssss", $u, $hash, $avatar, $color);

    if($st->execute()){
        echo "OK";
    }else{
        echo "EXISTS";
    }
    exit;
}

/* =========================
   LOGOUT
========================= */
if(isset($_POST['action']) && $_POST['action']==='logout'){
    session_destroy();
    echo "OK";
    exit;
}

/* =========================
   TOGGLE FAVORITO
========================= */
if(isset($_POST['action']) && $_POST['action']==='toggle_favorite'){

    if(!isset($_SESSION['user_id'])){
        echo "NO_LOGIN";
        exit;
    }

    $uid = $_SESSION['user_id'];
    $system = $_POST['system'] ?? '';
    $rom    = $_POST['rom'] ?? '';

    if($system==='' || $rom===''){
        echo "ERROR";
        exit;
    }

    $st = $conn->prepare(
        "SELECT id FROM favorites 
         WHERE user_id=? AND system=? AND rom=?"
    );
    $st->bind_param("iss", $uid, $system, $rom);
    $st->execute();
    $st->store_result();

    if($st->num_rows){
        $del = $conn->prepare(
            "DELETE FROM favorites 
             WHERE user_id=? AND system=? AND rom=?"
        );
        $del->bind_param("iss", $uid, $system, $rom);
        $del->execute();
        echo "REMOVED";
    }else{
        $ins = $conn->prepare(
            "INSERT INTO favorites(user_id,system,rom)
             VALUES(?,?,?)"
        );
        $ins->bind_param("iss", $uid, $system, $rom);
        $ins->execute();
        echo "ADDED";
    }
    exit;
}

/* =========================
   CAMBIAR AVATAR / COLOR
========================= */
if(isset($_POST['action']) && $_POST['action']==='change_avatar'){

    if(!isset($_SESSION['user_id'])){
        echo "NO_LOGIN";
        exit;
    }

    $uid = $_SESSION['user_id'];
    $avatar = $_POST['avatar'] !== "" ? $_POST['avatar'] : null;
    $color  = $_POST['color']  !== "" ? $_POST['color']  : null;

    $st = $conn->prepare(
        "UPDATE users 
         SET avatar=?, color=? 
         WHERE id=?"
    );
    $st->bind_param("ssi", $avatar, $color, $uid);
    $st->execute();

    $_SESSION['avatar'] = $avatar;
    $_SESSION['color']  = $color;

    echo "OK";
    exit;
}

/* =========================
   FETCH FAVORITOS (opcional)
========================= */
if(isset($_POST['action']) && $_POST['action']==='get_favorites'){

    if(!isset($_SESSION['user_id'])){
        echo json_encode([]);
        exit;
    }

    $uid = $_SESSION['user_id'];
    $out = [];

    $res = $conn->query(
        "SELECT system, rom 
         FROM favorites 
         WHERE user_id=$uid"
    );

    while($r = $res->fetch_assoc()){
        $out[] = $r['system']."::".$r['rom'];
    }

    echo json_encode($out);
    exit;
}
