<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

// Función para registrar actividad
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

// obtener ventas
if ($method == "GET") {
    if (isset($_GET['id'])) {
        // Venta específica
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
        // Todas las ventas activas
        $stmt = $pdo->query("
            SELECT s.*, CONCAT(p.first_name,' ',p.last_name) AS patient_name
            FROM sales s
            LEFT JOIN patients p ON s.id_patient = p.id_patient
            WHERE s.active = 1
            ORDER BY s.sale_date DESC
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

// soft delete
if ($method == "DELETE") {
    parse_str(file_get_contents('php://input'), $deleteVars);
    $saleId = (int)($_GET['id'] ?? $deleteVars['id'] ?? 0);

    if (!$saleId) {
        http_response_code(400);
        echo json_encode(["error" => "Sale ID required to delete"]);
        exit;
    }

    // Obtener estado anterior (active = 1 normalmente)
    $oldStmt = $pdo->prepare("SELECT active FROM sales WHERE id_sale = ?");
    $oldStmt->execute([$saleId]);
    $oldActive = $oldStmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE sales SET active = 0 WHERE id_sale = :id");
    $stmt->execute([':id' => $saleId]);

    // Registrar actividad (UPDATE active de 1 a 0)
    $oldData = ['active' => $oldActive];
    $newData = ['active' => 0];
    registerActivity($pdo, $currentUserId, 'UPDATE', 'sales', $saleId, $oldData, $newData);

    echo json_encode(["message" => "Sale logically deleted", "id_sale" => $saleId]);
    exit;
}

// editar venta
if ($method == "PATCH") {
    $data = json_decode(file_get_contents("php://input"), true);
    $saleId = (int)($_GET['id'] ?? $data['id'] ?? 0);
    $newStatus = (int)($data['id_sale_status'] ?? 0);

    if (!$saleId || !$newStatus) {
        http_response_code(400);
        echo json_encode(["error" => "Sale ID and status are required"]);
        exit;
    }

    $allowed = [1, 2, 3];
    if (!in_array($newStatus, $allowed)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid status"]);
        exit;
    }

    // Obtener estado anterior
    $oldStmt = $pdo->prepare("SELECT id_sale_status FROM sales WHERE id_sale = ?");
    $oldStmt->execute([$saleId]);
    $oldStatus = $oldStmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE sales SET id_sale_status = :status WHERE id_sale = :id");
    $stmt->execute([':status' => $newStatus, ':id' => $saleId]);

    // Registrar actividad (cambio de estado)
    $oldData = ['id_sale_status' => $oldStatus];
    $newData = ['id_sale_status' => $newStatus];
    registerActivity($pdo, $currentUserId, 'UPDATE', 'sales', $saleId, $oldData, $newData);

    echo json_encode(["message" => "Status updated", "id_sale" => $saleId, "id_sale_status" => $newStatus]);
    exit;
}

// crear venta
if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO sales
            (id_patient, id_user, subtotal, tax, total, payment_method, id_sale_status)
            VALUES (:patient, :user, :subtotal, :tax, :total, :payment_method, :status)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":patient" => $data["id_patient"],
        ":user"    => $data["id_user"] ?? null,
        ":subtotal"=> $data["subtotal"] ?? 0,
        ":tax"     => $data["tax"] ?? 0,
        ":total"   => $data["total"],
        ":payment_method" => $data["payment_method"] ?? "Cash",
        ":status"  => $data["id_sale_status"] ?? 1
    ]);

    $newId = $pdo->lastInsertId();

    // Registrar actividad
    $newData = [
        'id_patient'      => $data["id_patient"],
        'id_user'         => $data["id_user"] ?? null,
        'subtotal'        => $data["subtotal"] ?? 0,
        'tax'             => $data["tax"] ?? 0,
        'total'           => $data["total"],
        'payment_method'  => $data["payment_method"] ?? "Cash",
        'id_sale_status'  => $data["id_sale_status"] ?? 1
    ];
    registerActivity($pdo, $currentUserId, 'INSERT', 'sales', $newId, null, $newData);

    echo json_encode(["message" => "Sale created", "id_sale" => $newId]);
    exit;
}

// Si no se reconoce el método
http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
?>