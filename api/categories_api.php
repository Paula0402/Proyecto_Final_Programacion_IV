<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

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
$data = json_decode(file_get_contents("php://input"), true);

try {
    if ($method == "GET") {
        $stmt = $pdo->query("SELECT * FROM product_categories WHERE active = 1");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($method == "POST") {
        $stmt = $pdo->prepare("INSERT INTO product_categories (category_name) VALUES (:name)");
        $stmt->execute([":name" => $data["category_name"]]);
        $newId = $pdo->lastInsertId();

        $newData = ['category_name' => $data["category_name"]];
        registerActivity($pdo, $currentUserId, 'INSERT', 'product_categories', $newId, null, $newData);

        echo json_encode(["message" => "Category added successfully."]);
        exit;
    }

    if ($method == "PUT") {
        $oldStmt = $pdo->prepare("SELECT category_name FROM product_categories WHERE id_category = ?");
        $oldStmt->execute([$data["id_category"]]);
        $oldData = $oldStmt->fetch(PDO::FETCH_ASSOC);
        if (!$oldData) {
            throw new Exception("Category not found.");
        }

        $stmt = $pdo->prepare("UPDATE product_categories SET category_name = :name WHERE id_category = :id");
        $stmt->execute([
            ":name" => $data["category_name"],
            ":id"   => $data["id_category"]
        ]);

        $newData = ['category_name' => $data["category_name"]];
        registerActivity($pdo, $currentUserId, 'UPDATE', 'product_categories', $data["id_category"], $oldData, $newData);

        echo json_encode(["message" => "Category updated successfully"]);
        exit;
    }

    if ($method == "DELETE") {
        if (empty($data["id_category"])) {
            throw new Exception("Category ID not provided");
        }

        $id = (int)$data["id_category"];

        // Verificar existencia y estado
        $checkStmt = $pdo->prepare("SELECT category_name, active FROM product_categories WHERE id_category = ?");
        $checkStmt->execute([$id]);
        $categoria = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$categoria) {
            throw new Exception("Category not found");
        }
        if ($categoria['active'] == 0) {
            throw new Exception("The category is already inactive");
        }

        // Verificar productos activos
        $prodStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE id_category = ? AND active = 1");
        $prodStmt->execute([$id]);
        $productCount = $prodStmt->fetchColumn();

        if ($productCount > 0) {
            throw new Exception("Cannot disable the category because it has $productCount active product(s). To delete it, first deactivate the products (Edit product → Status = Inactive) or assign them to another category.");
        }

        // Desactivar (soft delete)
        $stmt = $pdo->prepare("UPDATE product_categories SET active = 0 WHERE id_category = ?");
        $stmt->execute([$id]);
        $rowsAffected = $stmt->rowCount();

        if ($rowsAffected > 0) {
            $oldData = ['active' => 1];
            $newData = ['active' => 0];
            registerActivity($pdo, $currentUserId, 'DEACTIVATE', 'product_categories', $id, $oldData, $newData);

            echo json_encode([
                "status" => "success",
                "message" => "Category disabled successfully"
            ]);
        } else {
            throw new Exception("Could not disable the category");
        }
        exit;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["message" => $e->getMessage()]); 
}
?>