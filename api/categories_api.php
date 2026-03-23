<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    if ($method == "GET") {
        $stmt = $pdo->query("SELECT * FROM product_categories");
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
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id_category = :id");
        $stmt->execute([":id" => $data["id_category"]]);
        echo json_encode(["message" => "Category deleted successfully"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}