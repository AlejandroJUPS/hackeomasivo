<?php
// auth.php - Login & Register (AJAX only)
session_start();
require_once __DIR__ . "/db.php";

if (!isset($_POST['action'])) {
    http_response_code(400);
    exit("BAD_REQUEST");
}

$action = $_POST['action'];

if ($action === "login") {
    $u = trim($_POST['username'] ?? "");
    $p = $_POST['password'] ?? "";

    if ($u === "" || $p === "") {
        exit("ERROR");
    }

    $st = $conn->prepare("SELECT id, password FROM users WHERE username=?");
    $st->bind_param("s", $u);
    $st->execute();
    $res = $st->get_result();
    $row = $res->fetch_assoc();

    if ($row && password_verify($p, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $u;
        exit("OK");
    }
    exit("ERROR");
}

if ($action === "register") {
    $u = trim($_POST['username'] ?? "");
    $p = $_POST['password'] ?? "";

    if ($u === "" || strlen($p) < 8) {
        exit("SHORT");
    }

    $hash = password_hash($p, PASSWORD_DEFAULT);
    $st = $conn->prepare("INSERT INTO users(username,password) VALUES(?,?)");
    $st->bind_param("ss", $u, $hash);

    if ($st->execute()) {
        exit("OK");
    } else {
        // Duplicate username or other SQL error
        exit("EXISTS");
    }
}

http_response_code(400);
exit("BAD_ACTION");
?>
