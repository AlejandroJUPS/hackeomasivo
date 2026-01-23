<?php
session_start();
require_once __DIR__ . "/db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "NO_LOGIN";
    exit;
}

if (!isset($_POST['system'], $_POST['rom'])) {
    http_response_code(400);
    echo "BAD_REQUEST";
    exit;
}

$user_id = $_SESSION['user_id'];
$system  = $_POST['system'];
$rom     = $_POST['rom'];

/* ¿Existe ya el favorito? */
$st = $conn->prepare(
    "SELECT id FROM favorites WHERE user_id=? AND system=? AND rom=?"
);
$st->bind_param("iss", $user_id, $system, $rom);
$st->execute();
$st->store_result();

if ($st->num_rows > 0) {
    /* BORRAR */
    $del = $conn->prepare(
        "DELETE FROM favorites WHERE user_id=? AND system=? AND rom=?"
    );
    $del->bind_param("iss", $user_id, $system, $rom);
    $del->execute();
    echo "REMOVED";
} else {
    /* INSERTAR */
    $ins = $conn->prepare(
        "INSERT INTO favorites (user_id, system, rom) VALUES (?, ?, ?)"
    );
    $ins->bind_param("iss", $user_id, $system, $rom);
    $ins->execute();
    echo "ADDED";
}
