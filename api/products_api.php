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
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

$currentUserId = $_SESSION['user_id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// lista productos
if ($method == "GET") {
    $sql = "SELECT p.*, c.category_name 
            FROM products p
            LEFT JOIN product_categories c ON p.id_category = c.id_category
            WHERE p.active = 1"; // Solo activos para la vista normal
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// crear producto
if ($method == "POST") {
    try {
        $sql = "INSERT INTO products (product_name, barcode, id_category, purchase_price, sale_price, min_stock, measurement_unit, active) 
                VALUES (:name, :barcode, :cat, :p_price, :s_price, :min, :unit, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name"    => $data["product_name"],
            ":barcode" => $data["barcode"],
            ":cat"     => $data["id_category"],
            ":p_price" => $data["purchase_price"],
            ":s_price" => $data["sale_price"],
            ":min"     => $data["min_stock"],
            ":unit"    => $data["measurement_unit"]
        ]);
        $newId = $pdo->lastInsertId();

        $newData = [
            'product_name'     => $data["product_name"],
            'barcode'          => $data["barcode"],
            'id_category'      => $data["id_category"],
            'purchase_price'   => $data["purchase_price"],
            'sale_price'       => $data["sale_price"],
            'min_stock'        => $data["min_stock"],
            'measurement_unit' => $data["measurement_unit"]
        ];
        registerActivity($pdo, $currentUserId, 'INSERT', 'products', $newId, null, $newData);

        echo json_encode(["message" => "Product created successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
    }
    exit;
}

// actualizar producto
if ($method == "PUT") {
    try {
        // Obtener valores antiguos
        $oldStmt = $pdo->prepare("SELECT product_name, barcode, purchase_price, sale_price, min_stock, measurement_unit FROM products WHERE id_product = ?");
        $oldStmt->execute([$data["id_product"]]);
        $oldData = $oldStmt->fetch(PDO::FETCH_ASSOC);
        if (!$oldData) {
            throw new Exception("Product not found");
        }

        // Actualizar producto
        $sql = "UPDATE products SET 
                product_name = :name, 
                barcode = :barcode,
                purchase_price = :purchase_price,
                sale_price = :sale_price, 
                min_stock = :min_stock,
                measurement_unit = :measurement_unit
                WHERE id_product = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name"    => $data["product_name"],
            ":barcode" => $data["barcode"],
            ":purchase_price" => $data["purchase_price"],
            ":sale_price" => $data["sale_price"],
            ":min_stock"   => $data["min_stock"],
            ":measurement_unit" => $data["measurement_unit"],
            ":id"      => $data["id_product"]
        ]);

        // detectar campos modificados
        $changes = [];
        $oldForLog = [];
        $newForLog = [];

        if ($oldData['product_name'] != $data['product_name']) {
            $changes[] = 'product_name';
            $oldForLog['product_name'] = $oldData['product_name'];
            $newForLog['product_name'] = $data['product_name'];
        }
        if ($oldData['barcode'] != $data['barcode']) {
            $changes[] = 'barcode';
            $oldForLog['barcode'] = $oldData['barcode'];
            $newForLog['barcode'] = $data['barcode'];
        }
        if ($oldData['purchase_price'] != $data['purchase_price']) {
            $changes[] = 'purchase_price';
            $oldForLog['purchase_price'] = $oldData['purchase_price'];
            $newForLog['purchase_price'] = $data['purchase_price'];
        }
        if ($oldData['sale_price'] != $data['sale_price']) {
            $changes[] = 'sale_price';
            $oldForLog['sale_price'] = $oldData['sale_price'];
            $newForLog['sale_price'] = $data['sale_price'];
        }
        if ($oldData['min_stock'] != $data['min_stock']) {
            $changes[] = 'min_stock';
            $oldForLog['min_stock'] = $oldData['min_stock'];
            $newForLog['min_stock'] = $data['min_stock'];
        }
        if ($oldData['measurement_unit'] != $data['measurement_unit']) {
            $changes[] = 'measurement_unit';
            $oldForLog['measurement_unit'] = $oldData['measurement_unit'];
            $newForLog['measurement_unit'] = $data['measurement_unit'];
        }

        // Solo registrar si hay cambios
        if (!empty($changes)) {
            $oldJson = json_encode($oldForLog);
            $newJson = json_encode($newForLog);
            registerActivity($pdo, $currentUserId, 'UPDATE', 'products', $data["id_product"], $oldJson, $newJson);
        }

        echo json_encode(["message" => "Product updated successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
    }
    exit;
}

// soft delete
if ($method == "DELETE") {
    try {
        $id = $data["id_product"];
        if (!$id) throw new Exception("Product ID not provided");

        // Obtener estado actual
        $oldStmt = $pdo->prepare("SELECT active FROM products WHERE id_product = ?");
        $oldStmt->execute([$id]);
        $oldActive = $oldStmt->fetchColumn();
        if ($oldActive === false) throw new Exception("Product not found");
        if ($oldActive == 0) throw new Exception("The product is already inactive");

        // Soft delete: cambiar active a 0
        $update = $pdo->prepare("UPDATE products SET active = 0 WHERE id_product = ?");
        $update->execute([$id]);

        // Registrar actividad
        $oldData = ['active' => 1];
        $newData = ['active' => 0];
        registerActivity($pdo, $currentUserId, 'DEACTIVATE', 'products', $id, $oldData, $newData);

        echo json_encode(["message" => "Product disabled successfully"]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["message" => $e->getMessage()]);
    }
    exit;
}
?>