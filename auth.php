<?php
session_start();
require_once "db.php";

/* =========================
   ACCIONES
========================= */
if (!isset($_POST['action'])) {
    exit;
}

/* =========================
   LOGIN
========================= */
if ($_POST['action'] === 'login') {

    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') {
        echo "ERROR";
        exit;
    }

    $st = $conn->prepare(
        "SELECT id, username, password, avatar, color 
         FROM users 
         WHERE username = ?"
    );
    $st->bind_param("s", $u);
    $st->execute();
    $res = $st->get_result();
    $row = $res->fetch_assoc();

    if ($row && password_verify($p, $row['password'])) {

        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['avatar']   = $row['avatar']; // puede ser NULL
        $_SESSION['color']    = $row['color'];  // puede ser NULL

        echo "OK";
    } else {
        echo "ERROR";
    }
    exit;
}

/* =========================
   REGISTER
========================= */
if ($_POST['action'] === 'register') {

    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    $avatar = $_POST['avatar'] ?? null; // ej: avatar_3
    $color  = $_POST['color']  ?? null; // ej: #ff0044

    if ($u === '' || strlen($p) < 8) {
        echo "SHORT";
        exit;
    }

    $hash = password_hash($p, PASSWORD_DEFAULT);

    $st = $conn->prepare(
        "INSERT INTO users (username, password, avatar, color)
         VALUES (?, ?, ?, ?)"
    );
    $st->bind_param("ssss", $u, $hash, $avatar, $color);

    if ($st->execute()) {
        echo "OK";
    } else {
        echo "EXISTS";
    }
    exit;
}

/* =========================
   LOGOUT
========================= */
if ($_POST['action'] === 'logout') {

    session_unset();
    session_destroy();

    echo "OK";
    exit;
}

/* =========================
   UPDATE AVATAR / COLOR
========================= */
if ($_POST['action'] === 'update_profile') {

    if (!isset($_SESSION['user_id'])) {
        echo "NO_LOGIN";
        exit;
    }

    $uid    = $_SESSION['user_id'];
    $avatar = $_POST['avatar'] ?? null;
    $color  = $_POST['color']  ?? null;

    $st = $conn->prepare(
        "UPDATE users SET avatar = ?, color = ? WHERE id = ?"
    );
    $st->bind_param("ssi", $avatar, $color, $uid);

    if ($st->execute()) {

        $_SESSION['avatar'] = $avatar;
        $_SESSION['color']  = $color;

        echo "OK";
    } else {
        echo "ERROR";
    }
    exit;
}

echo "INVALID";
