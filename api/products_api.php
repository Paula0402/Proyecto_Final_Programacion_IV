<?php
// Desactivar visualización de errores para que no rompan el JSON
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method == "GET") {
    $sql = "SELECT p.*, c.category_name 
            FROM products p
            LEFT JOIN product_categories c ON p.id_category = c.id_category";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method == "POST") {
    try {
        $sql = "INSERT INTO products (product_name, barcode, id_category, purchase_price, sale_price, min_stock, measurement_unit) 
                VALUES (:name, :barcode, :cat, :p_price, :s_price, :min, :unit)";
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
        echo json_encode(["message" => "Product created successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
    }
    exit;
}

if ($method == "PUT") {
    try {
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
            ":measurement_unit"    => $data["measurement_unit"],
            ":id"      => $data["id_product"]
        ]);
        echo json_encode(["message" => "Product updated successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
    }
    exit; 
}

if ($method == "DELETE") {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id_product = :id");
    $stmt->execute([":id" => $data["id_product"]]);
    echo json_encode(["message" => "Product deleted successfully"]);
    exit;
}