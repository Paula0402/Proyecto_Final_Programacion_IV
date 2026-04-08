<?php
header("Content-Type: application/json");
require_once "../config/db.php";

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == "GET") {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Sale ID required"]);
            exit;
        }

        $saleId = (int)$_GET['id'];

        // Get sale header info
        $stmt = $pdo->prepare("
            SELECT 
                s.id_sale,
                s.id_patient,
                s.id_user,
                s.id_sale_status,
                s.subtotal,
                s.tax,
                s.total,
                s.payment_method,
                s.sale_date,
                CONCAT(p.first_name,' ',p.last_name) AS patient_name,
                p.id_card,
                p.phone,
                u.full_name AS user_name,
                ss.status_name AS status
            FROM sales s
            LEFT JOIN patients p ON s.id_patient = p.id_patient
            LEFT JOIN users u ON s.id_user = u.id_user
            LEFT JOIN sale_statuses ss ON s.id_sale_status = ss.id_status
            WHERE s.id_sale = :id
        ");
        $stmt->execute([':id' => $saleId]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sale) {
            http_response_code(404);
            echo json_encode(["error" => "Sale not found"]);
            exit;
        }

        // Get sale details
        $stmt = $pdo->prepare("
            SELECT 
                sd.id_product,
                sd.quantity,
                sd.unit_price,
                sd.subtotal,
                p.product_name
            FROM sale_details sd
            LEFT JOIN products p ON sd.id_product = p.id_product
            WHERE sd.id_sale = :id
        ");
        $stmt->execute([':id' => $saleId]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sale['details'] = $details;

        http_response_code(200);
        echo json_encode($sale);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>
