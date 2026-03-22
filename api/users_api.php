<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    // --- OBTENER USUARIOS ---
    if ($method == "GET") {
        $stmt = $pdo->query("SELECT id_user, full_name, email, phone, id_role, active FROM users");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // --- CREAR USUARIO ---
    if ($method == "POST") {
        if (empty($data["full_name"]) || empty($data["email"])) {
            throw new Exception("Incomplete user data provided");
        }

        $sql = "INSERT INTO users (full_name, email, phone, password_hash, id_role, active)
                VALUES (:name, :email, :phone, :pass, :role, 1)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name"  => $data["full_name"],
            ":email" => $data["email"],
            ":phone" => $data["phone"],
            ":pass"  => password_hash($data["password"], PASSWORD_DEFAULT),
            ":role"  => $data["id_role"]
        ]);

        echo json_encode(["message" => "User created successfully"]);
    }

    // --- ACTUALIZAR USUARIO ---
    if ($method == "PUT") {
        if (empty($data["id_user"])) throw new Exception("ID of user not provided");

        $sql = "UPDATE users
                SET full_name = :name,
                    email = :email,
                    phone = :phone,
                    id_role = :role,
                    active = :active
                WHERE id_user = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":id"     => $data["id_user"],
            ":name"   => $data["full_name"],
            ":email"  => $data["email"],
            ":phone"  => $data["phone"],
            ":role"   => $data["id_role"],
            ":active" => $data["active"]
        ]);

        echo json_encode(["message" => "User updated successfully"]);
    }

    // --- ELIMINAR USUARIO ---
    if ($method == "DELETE") {
        if (empty($data["id_user"])) throw new Exception("ID of user not provided");

        $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
        $stmt->execute([":id" => $data["id_user"]]);

        echo json_encode(["message" => "User deleted successfully"]);
    }

} catch (Exception $e) {
    // Si algo falla, enviamos el error en formato JSON
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => "Error: " . $e->getMessage()
    ]);
}