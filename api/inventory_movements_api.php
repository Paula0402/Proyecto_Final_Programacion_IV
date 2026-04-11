<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];


if ($method == "GET") {
    $sql = "SELECT 
                m.id_movement, 
                u.full_name as user_name, 
                p.product_name, 
                t.type_name, 
                m.quantity, 
                m.justification, 
                m.movement_date
            FROM inventory_movements m
            JOIN products p ON m.id_batch = p.id_product
            JOIN movement_types t ON m.id_movement_type = t.id_type
            JOIN users u ON m.id_user = u.id_user
            ORDER BY m.movement_date DESC"; 
            
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
$id_batch = $data['id_product'];
$id_type = $data['id_type'];
$quantity = $data['quantity'];
$id_user = $data['id_user'];
$justification = $data['justification']; 

try {
    $pdo->beginTransaction();

    
    $sql = "INSERT INTO inventory_movements 
            (id_batch, id_movement_type, quantity, id_user, justification, movement_date) 
            VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_batch, $id_type, $quantity, $id_user, $justification]);

    // Lógica de actualización de stock (min_stock)
    if ($id_type == 1) {
        $sqlUpdate = "UPDATE products SET min_stock = min_stock + ? WHERE id_product = ?";
    } else {
        $sqlUpdate = "UPDATE products SET min_stock = min_stock - ? WHERE id_product = ?";
    }

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([$quantity, $id_batch]);

    $pdo->commit();
    echo json_encode(["message" => "Movement registered with justification!"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["error" => true, "message" => "Error: " . $e->getMessage()]);
}

}

if ($method == "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_mov = $data['id_movement'];

    try {
        $pdo->beginTransaction();

        // 1. Obtener datos antes de borrar para revertir stock
        $stmt = $pdo->prepare("SELECT id_batch, id_movement_type, quantity FROM inventory_movements WHERE id_movement = ?");
        $stmt->execute([$id_mov]);
        $mov = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mov) {
            // 2. Revertir: Si era entrada (1), restamos. Si era salida, sumamos.
            if ($mov['id_movement_type'] == 1) {
                $sqlRev = "UPDATE products SET min_stock = min_stock - ? WHERE id_product = ?";
            } else {
                $sqlRev = "UPDATE products SET min_stock = min_stock + ? WHERE id_product = ?";
            }
            $pdo->prepare($sqlRev)->execute([$mov['quantity'], $mov['id_batch']]);

            // 3. Borrar registro
            $pdo->prepare("DELETE FROM inventory_movements WHERE id_movement = ?")->execute([$id_mov]);
        }

        $pdo->commit();
        echo json_encode(["message" => "Eliminado y stock revertido"]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(["error" => true, "message" => $e->getMessage()]);
    }
    exit;
}