<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true) ?? [];

function batchExistsForProduct(PDO $pdo, int $idProduct, string $batchNumber, int $excludeId = 0): bool {
    $sql = "SELECT COUNT(*) FROM batches WHERE id_product = ? AND batch_number = ?";
    $params = [$idProduct, $batchNumber];

    if ($excludeId > 0) {
        $sql .= " AND id_batch <> ?";
        $params[] = $excludeId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

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

    if (batchExistsForProduct($pdo, $idProduct, $batchNumber)) {
        http_response_code(409);
        echo json_encode(["error" => true, "message" => "This batch number already exists for the selected product."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES (?, ?, CURDATE(), ?, ?, ?)");
        $stmt->execute([$idProduct, $batchNumber, $expirationDate, $initialQuantity, $currentQuantity]);
        echo json_encode(["message" => "Batch created successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => true, "message" => "Error creating batch."]);
    }

    exit;
}

if ($method === "PUT") {
    $idBatch = (int)($data["id_batch"] ?? 0);
    $idProduct = (int)($data["id_product"] ?? 0);
    $batchNumber = trim((string)($data["batch_number"] ?? ""));
    $expirationDate = $data["expiration_date"] ?? null;
    $initialQuantity = (int)($data["initial_quantity"] ?? 0);
    $currentQuantity = (int)($data["current_quantity"] ?? 0);

    if ($idBatch <= 0 || $idProduct <= 0 || $batchNumber === "" || !$expirationDate || $initialQuantity < 0 || $currentQuantity < 0) {
        http_response_code(400);
        echo json_encode(["error" => true, "message" => "Invalid batch data."]);
        exit;
    }

    if (batchExistsForProduct($pdo, $idProduct, $batchNumber, $idBatch)) {
        http_response_code(409);
        echo json_encode(["error" => true, "message" => "This batch number already exists for the selected product."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE batches SET id_product = ?, batch_number = ?, expiration_date = ?, initial_quantity = ?, current_quantity = ? WHERE id_batch = ?");
        $stmt->execute([$idProduct, $batchNumber, $expirationDate, $initialQuantity, $currentQuantity, $idBatch]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["error" => true, "message" => "Batch not found or no changes applied."]);
            exit;
        }

        echo json_encode(["message" => "Batch updated successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => true, "message" => "Error updating batch."]);
    }

    exit;
}

if ($method === "DELETE") {
    $idBatch = (int)($data["id_batch"] ?? 0);

    if ($idBatch <= 0) {
        http_response_code(400);
        echo json_encode(["error" => true, "message" => "Invalid batch ID."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM batches WHERE id_batch = ?");
        $stmt->execute([$idBatch]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["error" => true, "message" => "Batch not found."]);
            exit;
        }

        echo json_encode(["message" => "Batch deleted successfully"]);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(["error" => true, "message" => "This batch cannot be deleted because it is used in movements or sales."]);
    }

    exit;
}

http_response_code(405);
echo json_encode(["error" => true, "message" => "Method not allowed."]);
