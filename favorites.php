<?php
// favorites.php - Toggle & fetch favorites (AJAX)
session_start();
require_once __DIR__ . "/db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("NO_LOGIN");
}

$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $action = $_POST['action'] ?? "";

    if ($action === "toggle") {
        $system = $_POST['system'] ?? "";
        $rom = $_POST['rom'] ?? "";

        if ($system === "" || $rom === "") {
            exit("ERROR");
        }

        $st = $conn->prepare(
            "SELECT id FROM favorites WHERE user_id=? AND system=? AND rom=?"
        );
        $st->bind_param("iss", $uid, $system, $rom);
        $st->execute();
        $st->store_result();

        if ($st->num_rows > 0) {
            $del = $conn->prepare(
                "DELETE FROM favorites WHERE user_id=? AND system=? AND rom=?"
            );
            $del->bind_param("iss", $uid, $system, $rom);
            $del->execute();
            exit("REMOVED");
        } else {
            $ins = $conn->prepare(
                "INSERT INTO favorites(user_id, system, rom) VALUES(?,?,?)"
            );
            $ins->bind_param("iss", $uid, $system, $rom);
            $ins->execute();
            exit("ADDED");
        }
    }

    exit("BAD_ACTION");
}

// GET: fetch favorites
$res = $conn->query("SELECT system, rom FROM favorites WHERE user_id=$uid");
$favs = [];
while ($r = $res->fetch_assoc()) {
    $favs[] = $r['system'] . "::" . $r['rom'];
}
header("Content-Type: application/json");
echo json_encode($favs);
?>
