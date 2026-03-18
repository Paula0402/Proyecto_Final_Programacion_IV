<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method == "GET") {
    $sql = "SELECT m.id_movement, p.name_product, t.name_type, m.quantity, m.date_movement
            FROM inventory_movements m
            JOIN products p ON m.id_product = p.id_product
            JOIN movement_types t ON m.id_type = t.id_type";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method == "POST") {
    $product = $data["id_product"];
    $type_id = $data["id_type"]; // Recibimos el ID numérico del select
    $qty     = $data["quantity"];

    try {
        $pdo->beginTransaction();

        // Actualizar stock basado en el ID de tipo (1 = Entrada, 2 = Salida)
        if ($type_id == 1) {
            $sqlStock = "UPDATE products SET stock = stock + :qty WHERE id_product = :id";
        } else {
            $sqlStock = "UPDATE products SET stock = stock - :qty WHERE id_product = :id";
        }

        $stmtStock = $pdo->prepare($sqlStock);
        $stmtStock->execute([":qty" => $qty, ":id" => $product]);

        // Registrar el movimiento
        $sqlMov = "INSERT INTO inventory_movements (id_product, id_type, quantity, movement_date)
                   VALUES (:id, :type, :qty, NOW())";
        $stmtMov = $pdo->prepare($sqlMov);
        $stmtMov->execute([
            ":id"   => $product,
            ":type" => $type_id,
            ":qty"  => $qty
        ]);

        $pdo->commit();
        echo json_encode(["message" => "Movimiento registrado correctamente"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
    }
}