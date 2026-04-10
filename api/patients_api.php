<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// Obtener lista de pacientes (activos e inactivos si all=1)
if ($method == "GET") {
    $include_inactive = isset($_GET['all']) ? $_GET['all'] : '1';
    if ($include_inactive == '1') {
        $stmt = $pdo->query("SELECT * FROM patients ORDER BY first_name, last_name");
    } else {
        $stmt = $pdo->query("SELECT * FROM patients WHERE active = 1 ORDER BY first_name, last_name");
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// Crear un nuevo paciente
if ($method == "POST") {
    $id_card = trim($data['id_card'] ?? '');
    $first_name = trim($data['first_name'] ?? '');
    $last_name = trim($data['last_name'] ?? '');
    $birth_date = !empty($data['birth_date']) ? $data['birth_date'] : null;
    $phone = trim($data['phone'] ?? '');
    $email = trim($data['email'] ?? '');
    $address = trim($data['address'] ?? '');

    if (empty($id_card) || empty($first_name) || empty($last_name)) {
        http_response_code(400);
        echo json_encode(["error" => "ID Card, First Name and Last Name are required"]);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid email format"]);
        exit;
    }

    if (!empty($phone) && !preg_match('/^[0-9]{8,15}$/', $phone)) {
        http_response_code(400);
        echo json_encode(["error" => "Phone must contain 8-15 digits"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO patients (id_card, first_name, last_name, birth_date, phone, email, address, active) 
                                VALUES (:id_card, :first_name, :last_name, :birth_date, :phone, :email, :address, 1)");
        $stmt->execute([
            ':id_card' => $id_card,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':birth_date' => $birth_date,
            ':phone' => $phone,
            ':email' => $email,
            ':address' => $address
        ]);
        echo json_encode(["message" => "Patient created successfully", "id" => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        if ($e->errorInfo[1] == 1062) {
            echo json_encode(["error" => "ID Card or Email already exists"]);
        } else {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

// Actualizar datos de un paciente
if ($method == "PUT") {
    $id = (int)($data['id_patient'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Patient ID required"]);
        exit;
    }

    $id_card = trim($data['id_card'] ?? '');
    $first_name = trim($data['first_name'] ?? '');
    $last_name = trim($data['last_name'] ?? '');
    $birth_date = !empty($data['birth_date']) ? $data['birth_date'] : null;
    $phone = trim($data['phone'] ?? '');
    $email = trim($data['email'] ?? '');
    $address = trim($data['address'] ?? '');
    $active = isset($data['active']) ? (int)$data['active'] : 1;

    if (empty($id_card) || empty($first_name) || empty($last_name)) {
        http_response_code(400);
        echo json_encode(["error" => "Required fields missing"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE patients SET 
                                id_card = :id_card,
                                first_name = :first_name,
                                last_name = :last_name,
                                birth_date = :birth_date,
                                phone = :phone,
                                email = :email,
                                address = :address,
                                active = :active
                                WHERE id_patient = :id");
        $stmt->execute([
            ':id_card' => $id_card,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':birth_date' => $birth_date,
            ':phone' => $phone,
            ':email' => $email,
            ':address' => $address,
            ':active' => $active,
            ':id' => $id
        ]);
        echo json_encode(["message" => "Patient updated successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

// Activar o desactivar paciente (soft delete)
if ($method == "DELETE") {
    $id = (int)($data['id_patient'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Patient ID required"]);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT active FROM patients WHERE id_patient = :id");
    $stmt->execute([':id' => $id]);
    $current = $stmt->fetchColumn();
    
    if ($current === false) {
        http_response_code(404);
        echo json_encode(["error" => "Patient not found"]);
        exit;
    }
    
    $newStatus = $current == 1 ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE patients SET active = :active WHERE id_patient = :id");
    $stmt->execute([':active' => $newStatus, ':id' => $id]);
    
    $message = $newStatus == 1 ? "Patient activated successfully" : "Patient deactivated successfully";
    echo json_encode(["message" => $message, "active" => $newStatus]);
}
?>