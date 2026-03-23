<?php
ob_start(); // Captura cualquier salida accidental para no romper el JSON
header("Content-Type: application/json");
require_once "../config/db.php";

// Asegurar que PDO lance excepciones si hay errores de SQL
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {

    $stmt = $pdo->query("
        SELECT id_user, full_name, email, id_role
        FROM users
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

        echo json_encode(["message" => "User created successfully"]);
    }

    // --- ACTUALIZAR USUARIO ---
    if ($method == "PUT") {
        if (empty($data["id_user"])) throw new Exception("ID of user not provided");

        // Validación de Email en edición
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Validación de Teléfono en edición
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

    // --- ELIMINAR USUARIO ---
    if ($method == "DELETE") {
        if (empty($data["id_user"])) throw new Exception("ID of user not provided");

        $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
        $stmt->execute([":id" => $data["id_user"]]);

        echo json_encode(["message" => "User deleted successfully"]);
    }

} catch (Exception $e) {
    // Si algo falla, limpiamos cualquier salida previa y enviamos el error JSON
    ob_clean(); 
    http_response_code(400); // Cambiado a 400 para errores de validación
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
ob_end_flush(); // Envía el contenido del buffer
