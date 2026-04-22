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

function resolveMovementDirection(string $typeName): int {
    $name = strtolower(trim($typeName));

    $inboundKeywords = ['purchase', 'entrada', 'ingreso', 'restock', 'replenishment'];
    foreach ($inboundKeywords as $keyword) {
        if (strpos($name, $keyword) !== false) {
            return 1;
        }
    }

    $outboundKeywords = ['sale', 'salida', 'internal use', 'uso interno', 'adjustment', 'loss', 'expiry', 'expir', 'damage', 'theft'];
    foreach ($outboundKeywords as $keyword) {
        if (strpos($name, $keyword) !== false) {
            return -1;
        }
    }

    return 0;
}

// obtener movimentos
if ($method == "GET") {
    $sql = "SELECT 
                m.id_movement, 
                u.full_name as user_name, 
                b.id_batch,
                p.product_name, 
                t.type_name, 
                m.quantity, 
                m.justification, 
                m.movement_date
            FROM inventory_movements m
            JOIN batches b ON m.id_batch = b.id_batch
            JOIN products p ON b.id_product = p.id_product
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
    
    $id_batch = (int)($data['id_batch'] ?? ($data['id_product'] ?? 0));
    $id_type = (int)($data['id_type'] ?? 0);
    $quantity = (int)($data['quantity'] ?? 0);
    $id_user = (int)($data['id_user'] ?? 0);
    $justification = trim((string)($data['justification'] ?? ''));

    try {
        if ($id_batch <= 0 || $id_type <= 0 || $id_user <= 0 || $quantity <= 0) {
            throw new Exception("Invalid movement data.");
        }

        $stmtBatch = $pdo->prepare("SELECT current_quantity FROM batches WHERE id_batch = ?");
        $stmtBatch->execute([$id_batch]);
        $batch = $stmtBatch->fetch(PDO::FETCH_ASSOC);

        if (!$batch) {
            throw new Exception("The selected batch does not exist.");
        }

        $stmtType = $pdo->prepare("SELECT type_name FROM movement_types WHERE id_type = ?");
        $stmtType->execute([$id_type]);
        $movementType = $stmtType->fetch(PDO::FETCH_ASSOC);

        if (!$movementType) {
            throw new Exception("The selected movement type does not exist.");
        }

        $direction = resolveMovementDirection((string)$movementType['type_name']);
        if ($direction === 0) {
            throw new Exception("This movement type has no stock direction configured.");
        }

        if ($direction === -1 && (int)$batch['current_quantity'] < $quantity) {
            throw new Exception("Insufficient stock in the selected batch.");
        }

        $pdo->beginTransaction();

        $movementQuantity = $quantity * $direction;

        $sql = "INSERT INTO inventory_movements 
                (id_batch, id_movement_type, quantity, id_user, justification, movement_date) 
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_batch, $id_type, $movementQuantity, $id_user, $justification]);
        $newId = $pdo->lastInsertId();

        // Actualizar stock del lote usando la cantidad firmada
        $sqlUpdate = "UPDATE batches SET current_quantity = current_quantity + ? WHERE id_batch = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$movementQuantity, $id_batch]);

        $pdo->commit();

        // Registrar actividad
        $newData = [
            'id_batch' => $id_batch,
            'id_movement_type' => $id_type,
            'quantity' => $movementQuantity,
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

        if (!$mov) {
            throw new Exception("Movement not found");
        }

        // Verificar si el movimiento está referenciado en sale_details (opcional, pero más clara)
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM sale_details WHERE id_movement = ?");
        $checkStmt->execute([$id_mov]);
        $usedInSale = $checkStmt->fetchColumn() > 0;

        if ($usedInSale) {
            throw new Exception("This movement cannot be deleted because it is associated with a sale. To delete it, you must first cancel the related sale..");
        }

        // Registrar actividad antes de borrar
        registerActivity($pdo, $currentUserId, 'DELETE', 'inventory_movements', $id_mov, $mov, null);

        // Revertir el efecto en stock invirtiendo la cantidad firmada del movimiento
        $sqlRev = "UPDATE batches SET current_quantity = current_quantity - ? WHERE id_batch = ?";
        $pdo->prepare($sqlRev)->execute([$mov['quantity'], $mov['id_batch']]);

        // Eliminar el movimiento
        $pdo->prepare("DELETE FROM inventory_movements WHERE id_movement = ?")->execute([$id_mov]);

        $pdo->commit();
        echo json_encode(["message" => "Movement deleted and stock reversed."]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        
        // Detectar violación de clave foránea (error 1451 en MySQL)
        if ($e->errorInfo[1] == 1451) {
            echo json_encode([
                "error" => true,
                "message" => "This movement cannot be deleted because it is associated with a sale. To delete it, you must first cancel the related sale."
            ]);
        } else {
            echo json_encode(["error" => true, "message" => "Database error: " . $e->getMessage()]);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(["error" => true, "message" => $e->getMessage()]);
    }
    exit;
}
?>