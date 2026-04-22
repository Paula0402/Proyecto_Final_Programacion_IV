<?php
session_start(); 
header("Content-Type: application/json");
require_once "../config/db.php";

// registrar actividad 
function registerActivity($pdo, $userId, $action, $table, $recordId, $oldValue = null, $newValue = null) {
    try {
        if (is_array($oldValue) || is_object($oldValue)) {
            $oldValue = json_encode($oldValue, JSON_UNESCAPED_UNICODE);
        }
        if (is_array($newValue) || is_object($newValue)) {
            $newValue = json_encode($newValue, JSON_UNESCAPED_UNICODE);
        }
        $stmt = $pdo->prepare("CALL sp_activity_logs_create(?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $table, $recordId, $oldValue, $newValue]);
        return true;
    } catch (PDOException $e) {
        error_log("Error recording activity: " . $e->getMessage());
        return false;
    }
}

$currentUserId = $_SESSION['user_id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

// obtener movimentos
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

// crear movimiento
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
        $newId = $pdo->lastInsertId();

        // Actualizar stock en products.min_stock
        if ($id_type == 1) { // Entrada
            $sqlUpdate = "UPDATE products SET min_stock = min_stock + ? WHERE id_product = ?";
        } else { // Salida
            $sqlUpdate = "UPDATE products SET min_stock = min_stock - ? WHERE id_product = ?";
        }
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$quantity, $id_batch]);

        $pdo->commit();

        // Registrar actividad
        $newData = [
            'id_batch' => $id_batch,
            'id_movement_type' => $id_type,
            'quantity' => $quantity,
            'id_user' => $id_user,
            'justification' => $justification
        ];
        registerActivity($pdo, $currentUserId, 'INSERT', 'inventory_movements', $newId, null, $newData);

        echo json_encode(["message" => "Movement recorded."]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(["error" => true, "message" => "Error: " . $e->getMessage()]);
    }
    exit;
}

// eliminar movimiento
if ($method == "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_mov = $data['id_movement'];

    try {
        $pdo->beginTransaction();

        // Obtener datos del movimiento antes de borrar
        $stmt = $pdo->prepare("SELECT id_batch, id_movement_type, quantity FROM inventory_movements WHERE id_movement = ?");
        $stmt->execute([$id_mov]);
        $mov = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mov) {
            // Registrar actividad 
            registerActivity($pdo, $currentUserId, 'DELETE', 'inventory_movements', $id_mov, $mov, null);

            // Revertir el efecto en el stock
            if ($mov['id_movement_type'] == 1) { 
                $sqlRev = "UPDATE products SET min_stock = min_stock - ? WHERE id_product = ?";
            } else {
                $sqlRev = "UPDATE products SET min_stock = min_stock + ? WHERE id_product = ?";
            }
            $pdo->prepare($sqlRev)->execute([$mov['quantity'], $mov['id_batch']]);

            // Eliminar el movimiento
            $pdo->prepare("DELETE FROM inventory_movements WHERE id_movement = ?")->execute([$id_mov]);
        }

        $pdo->commit();
        echo json_encode(["message" => "Movement deleted and stock reverted."]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(["error" => true, "message" => $e->getMessage()]);
    }
    exit;
}
?>