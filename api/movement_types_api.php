<?php
header("Content-Type: application/json");
require_once "../config/db.php"; 

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    // --- OBTENER TODOS LOS TIPOS ---
    if ($method == "GET") {
        $stmt = $pdo->query("SELECT * FROM movement_types");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
    }

    // --- CREAR NUEVO TIPO ---
    if ($method == "POST") {
        $sql = "INSERT INTO movement_types (type_name) VALUES (:name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name" => $data["type_name"]
        ]);
        echo json_encode(["message" => "Movement type added successfully"]);
    }

    // --- ACTUALIZAR TIPO ---
    if ($method == "PUT") {
        $sql = "UPDATE movement_types SET type_name = :name WHERE id_type = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name" => $data["type_name"],
            ":id"   => $data["id_type"]
        ]);
        echo json_encode(["message" => "Type updated successfully"]);
    }

    // --- ELIMINAR TIPO ---
    if ($method == "DELETE") {
        $sql = "DELETE FROM movement_types WHERE id_type = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":id" => $data["id_type"]
        ]);
        echo json_encode(["message" => "Type deleted successfully"]);
    }

} catch (PDOException $e) {
    // En caso de error, devolvemos un JSON con el detalle
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => "Error in the base date: " . $e->getMessage()
    ]);
}