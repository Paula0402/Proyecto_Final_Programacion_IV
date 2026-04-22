<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true) ?? [];

if ($method === "GET") {
    $sql = "SELECT b.id_batch, b.id_product, b.batch_number, b.expiration_date, b.initial_quantity, b.current_quantity, p.product_name
            FROM batches b
            INNER JOIN products p ON b.id_product = p.id_product
            ORDER BY p.product_name ASC, b.expiration_date ASC";

    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === "POST") {
    $idProduct = (int)($data["id_product"] ?? 0);
    $batchNumber = trim((string)($data["batch_number"] ?? ""));
    $expirationDate = $data["expiration_date"] ?? null;
    $initialQuantity = (int)($data["initial_quantity"] ?? 0);
    $currentQuantity = (int)($data["current_quantity"] ?? $initialQuantity);

    if ($idProduct <= 0 || $batchNumber === "" || !$expirationDate || $initialQuantity < 0 || $currentQuantity < 0) {
        http_response_code(400);
        echo json_encode(["error" => true, "message" => "Invalid batch data."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO batches (id_product, batch_number, expiration_date, initial_quantity, current_quantity) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$idProduct, $batchNumber, $expirationDate, $initialQuantity, $currentQuantity]);

    echo json_encode(["message" => "Batch created successfully"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => true, "message" => "Method not allowed."]);
