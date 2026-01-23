<?php
session_start();
require_once __DIR__."/db.php";

/* =========================
   LOGOUT
========================= */
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_unset();
    session_destroy();
    echo "OK";
    exit;
}

/* =========================
   LOGIN
========================= */
if (isset($_POST['action']) && $_POST['action'] === 'login') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $st = $conn->prepare("SELECT id,password FROM users WHERE username=?");
    $st->bind_param("s", $username);
    $st->execute();
    $res = $st->get_result();
    $row = $res->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $username;
        echo "OK";
    } else {
        echo "ERROR";
    }
    exit;
}

/* =========================
   REGISTER
========================= */
if (isset($_POST['action']) && $_POST['action'] === 'register') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 8) {
        echo "SHORT";
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $st = $conn->prepare("INSERT INTO users(username,password) VALUES(?,?)");
    $st->bind_param("ss", $username, $hash);

    if ($st->execute()) {
        echo "OK";
    } else {
        echo "EXISTS";
    }
    exit;
}
