<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    $stmt = $pdo->query("
        SELECT id_status, status_name
        FROM sale_statuses
        ORDER BY status_name
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
