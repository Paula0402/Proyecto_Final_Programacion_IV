<?php
ob_start(); // Captura cualquier salida accidental para no romper el JSON
header("Content-Type: application/json");
require_once "../config/db.php";

// Asegurar que PDO lance excepciones si hay errores de SQL
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    // --- OBTENER USUARIOS ---
    if ($method == "GET") {
        // Añadí recovery_code a la consulta por si necesitas consultarlo desde el admin
        $stmt = $pdo->query("SELECT id_user, full_name, email, phone, id_role, active, recovery_code FROM users WHERE active = 1");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // --- CREAR USUARIO ---
    if ($method == "POST") {
        if (empty($data["full_name"]) || empty($data["email"]) || empty($data["phone"]) || empty($data["password"])) {
            throw new Exception("All fields are required.");
        }

        // Validación de Email
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Validación de Teléfono (8 a 15 números)
        if (!preg_match('/^[0-9]{8,15}$/', $data["phone"])) {
            throw new Exception("Phone must contain 8-15 digits.");
        }

        // --- GENERACIÓN AUTOMÁTICA DEL CÓDIGO ---
        $recovery_code = rand(1000, 9999);

        $sql = "INSERT INTO users (full_name, email, phone, password_hash, id_role, recovery_code, active)
                VALUES (:name, :email, :phone, :pass, :role, :code, 1)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name"  => $data["full_name"],
            ":email" => $data["email"],
            ":phone" => $data["phone"],
            ":pass"  => password_hash($data["password"], PASSWORD_DEFAULT),
            ":role"  => $data["id_role"],
            ":code"  => $recovery_code
        ]);

        // Retornamos el código en el mensaje para que el Admin pueda verlo
        echo json_encode([
            "message" => "User created successfully. Recovery Code: " . $recovery_code,
            "recovery_code" => $recovery_code
        ]);
    }

    // --- ACTUALIZAR USUARIO ---
    if ($method == "PUT") {
        if (empty($data["id_user"])) throw new Exception("ID of user not provided");

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        if (!preg_match('/^[0-9]{8,15}$/', $data["phone"])) {
            throw new Exception("Phone must contain 8-15 digits.");
        }

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

 // --- DESACTIVAR USUARIO (SOFT DELETE) ---
if ($method == "DELETE") {
    // 1. Validar que recibimos el ID
    if (empty($data["id_user"])) {
        throw new Exception("ID of user not provided");
    }

    try {
        // Este procedimiento solo hace un UPDATE active = 0, no borra la fila
        $stmt = $pdo->prepare("CALL sp_users_deactivate(:id)");
        $stmt->execute([":id" => $data["id_user"]]);
        
        // Obtenemos el conteo de filas afectadas desde el procedimiento
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['rows_affected'] > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "User deactivated successfully (Soft Delete)"
            ]);
        } else {
            // Si rows_affected es 0, es porque el ID no existe o ya estaba desactivado
            throw new Exception("User not found or already inactive");
        }

    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Could not deactivate user",
            "error" => $e->getMessage()
        ]);
    }
}

} catch (Exception $e) {
    ob_clean(); 
    http_response_code(400); 
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
ob_end_flush();