<?php
ob_start(); // Captura cualquier salida accidental para no romper el JSON
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

// Asegurar que PDO lance excepciones si hay errores de SQL
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Función para registrar actividad en activity_logs
function registerActivity($pdo, $userId, $action, $table, $recordId, $oldValue = null, $newValue = null) {
    try {
        // Convertir arrays/objetos a JSON para almacenarlos en TEXT
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
        // Registrar error interno sin detener la operación principal
        error_log("Error al registrar actividad: " . $e->getMessage());
        return false;
    }
}

// Obtener ID del usuario que realiza la acción (desde sesión)
$currentUserId = $_SESSION['user_id'] ?? null;  // si no hay sesión, queda null

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

        $newUserId = $pdo->lastInsertId(); // <--- obtener ID del usuario creado

        // Registrar actividad (INSERT)
        $newData = [                                       // <--- NUEVO
        'full_name' => $data["full_name"],
        'email'     => $data["email"],
        'phone'     => $data["phone"],
        'id_role'   => $data["id_role"]
        ];

        registerActivity($pdo, $currentUserId, 'INSERT', 'users', $newUserId, null, $newData);

        // Retornamos el código en el mensaje para que el Admin pueda verlo
        echo json_encode([
            "message" => "User created successfully. Recovery Code: " . $recovery_code,
            "recovery_code" => $recovery_code
        ]);
    }

    // --- ACTUALIZAR USUARIO ---
    if ($method == "PUT") {
        if (empty($data["id_user"])) throw new Exception("ID of user not provided");

        // Obtener valores antiguos antes de actualizar
        $stmtOld = $pdo->prepare("SELECT full_name, email, phone, id_role, active FROM users WHERE id_user = ?");
        $stmtOld->execute([$data["id_user"]]);
        $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if (!$oldData) {
            throw new Exception("User not found");
        }

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

        // <--- egistrar actividad
        $newData = [
            'full_name' => $data["full_name"],
            'email'     => $data["email"],
            'phone'     => $data["phone"],
            'id_role'   => $data["id_role"],
            'active'    => $data["active"]
        ];
        registerActivity($pdo, $currentUserId, 'UPDATE', 'users', $data["id_user"], $oldData, $newData);

        echo json_encode(["message" => "User updated successfully"]);
    }

// --- DESACTIVAR USUARIO (SOFT DELETE) ---
if ($method == "DELETE") {
    if (empty($data["id_user"])) {
        throw new Exception("ID of user not provided");
    }

    // Obtener datos actuales antes de desactivar
    $stmtOld = $pdo->prepare("SELECT active, full_name, email FROM users WHERE id_user = ?");
    $stmtOld->execute([$data["id_user"]]);
    $user = $stmtOld->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception("User not found");
    }
    if ($user['active'] == 0) {
        throw new Exception("User is already inactive");
    }

    try {
        $stmt = $pdo->prepare("CALL sp_users_deactivate(:id)");
        $stmt->execute([":id" => $data["id_user"]]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['rows_affected'] > 0) {
            // Registrar actividad
            $oldData = ['active' => 1];
            $newData = ['active' => 0];
            registerActivity($pdo, $currentUserId, 'DEACTIVATE', 'users', $data["id_user"], $oldData, $newData);

            echo json_encode([
                "status" => "success",
                "message" => "User deactivated successfully (Soft Delete)"
            ]);
        } else {
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