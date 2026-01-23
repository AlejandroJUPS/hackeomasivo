<?php
// db.php - Database connection
$DB_HOST = "sql103.infinityfree.com";
$DB_USER = "if0_40909083";
$DB_PASS = "REEMPLAZA_TU_PASSWORD";
$DB_NAME = "if0_40909083_emutable";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    exit("DB_ERROR");
}
$conn->set_charset("utf8mb4");
?>
