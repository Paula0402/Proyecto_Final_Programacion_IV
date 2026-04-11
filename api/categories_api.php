<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    if ($method == "GET") {
        $stmt = $pdo->query("SELECT * FROM product_categories WHERE active = 1");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($method == "POST") {
        $stmt = $pdo->prepare("INSERT INTO product_categories (category_name) VALUES (:name)");
        $stmt->execute([":name" => $data["category_name"]]);
        echo json_encode(["message" => "Category added successfully"]);
    }

    if ($method == "PUT") {
        $stmt = $pdo->prepare("UPDATE product_categories SET category_name = :name WHERE id_category = :id");
        $stmt->execute([
            ":name" => $data["category_name"],
            ":id"   => $data["id_category"]
        ]);
        echo json_encode(["message" => "Category updated successfully"]);
    }

 if ($method == "DELETE") {
    if (empty($data["id_category"])) {
        throw new Exception("ID of category not provided");
    }

    try {
        // Este procedimiento solo hace un UPDATE active = 0, no borra la fila
        $stmt = $pdo->prepare("CALL sp_product_categories_deactivate(:id)");
        $stmt->execute([":id" => $data["id_category"]]);
        
        // Obtenemos el conteo de filas afectadas desde el procedimiento
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['rows_affected'] > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Category deactivated successfully (Soft Delete)"
            ]);
        } else {
            // Si rows_affected es 0, es porque el ID no existe o ya estaba desactivado
            throw new Exception("Category not found or already inactive");
        }

    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Could not deactivate category",
            "error" => $e->getMessage()
        ]);
    }
}
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}