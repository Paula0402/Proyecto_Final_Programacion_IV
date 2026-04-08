<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    if (isset($_GET['id'])) {
        // Get specific sale
        $saleId = (int)$_GET['id'];
        $stmt = $pdo->prepare("
            SELECT s.*, CONCAT(p.first_name,' ',p.last_name) AS patient_name
            FROM sales s
            LEFT JOIN patients p ON s.id_patient = p.id_patient
            WHERE s.id_sale = :id
        ");
        $stmt->execute([':id' => $saleId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        // Get all active sales
        $stmt = $pdo->query("
            SELECT s.*, CONCAT(p.first_name,' ',p.last_name) AS patient_name
            FROM sales s
            LEFT JOIN patients p ON s.id_patient = p.id_patient
            WHERE s.active = 1
            ORDER BY s.sale_date DESC
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

if ($method == "DELETE") {
    parse_str(file_get_contents('php://input'), $deleteVars);
    $saleId = (int)($_GET['id'] ?? $deleteVars['id'] ?? 0);

    if (!$saleId) {
        http_response_code(400);
        echo json_encode(["error" => "Sale ID required for deletion"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE sales SET active = 0 WHERE id_sale = :id");
    $stmt->execute([':id' => $saleId]);

    echo json_encode(["message" => "Sale soft deleted", "id_sale" => $saleId]);
}

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO sales
            (id_patient, id_user, subtotal, tax, total, payment_method, id_sale_status)
            VALUES (:patient, :user, :subtotal, :tax, :total, :payment_method, :status)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":patient" => $data["id_patient"],
        ":user" => $data["id_user"] ?? null,
        ":subtotal" => $data["subtotal"] ?? 0,
        ":tax" => $data["tax"] ?? 0,
        ":total" => $data["total"],
        ":payment_method" => $data["payment_method"] ?? "Cash",
        ":status" => $data["id_sale_status"] ?? 1
    ]);

    echo json_encode(["message" => "sale created", "id_sale" => $pdo->lastInsertId()]);
}
?>