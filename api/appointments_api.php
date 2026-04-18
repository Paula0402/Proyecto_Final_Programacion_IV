<?php
ob_start();
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Función para registrar actividad
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
        error_log("Error al registrar actividad: " . $e->getMessage());
        return false;
    }
}

// Obtener ID del usuario logueado
$currentUserId = $_SESSION['user_id'] ?? null;

// Obtener datos (soporta JSON y POST tradicional)
$input = file_get_contents("php://input");
$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $data = $_POST;
}

$method = $_SERVER['REQUEST_METHOD'];

// --- OBTENER CITAS ---
if ($method == "GET") {
    $stmt = $pdo->query("
        SELECT a.*, 
               CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
               u.full_name AS doctor_name,
               s.status_name AS status
        FROM appointments a
        JOIN patients p ON a.id_patient = p.id_patient
        JOIN users u ON a.id_dentist_user = u.id_user
        JOIN appointment_statuses s ON a.id_appointment_status = s.id_status
        ORDER BY a.appointment_date DESC, a.appointment_time
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// --- PROCESAR POST (crear o cerrar cita) ---
if ($method == "POST") {
    $action = $data['action'] ?? '';

    // --- CREAR CITA ---
    if ($action == 'add_appointment') {
        $patient_id = $data['patient_id'] ?? 0;
        $doctor_id  = $data['assigned_user'] ?? 0;
        $scheduled_at = $data['scheduled_at'] ?? '';
        $reason = $data['reason'] ?? '';

        if (!$patient_id || !$doctor_id || !$scheduled_at) {
            http_response_code(400);
            echo json_encode(["error" => "Patient, doctor and date are required"]);
            exit;
        }

        $dateTime = new DateTime($scheduled_at);
        $appointment_date = $dateTime->format('Y-m-d');
        $appointment_time = $dateTime->format('H:i:s');
        $duration_minutes = 30;
        $id_status = 1; // Scheduled

        try {
            $sql = "INSERT INTO appointments (id_patient, id_dentist_user, appointment_date, appointment_time, reason, id_appointment_status, duration_minutes)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $reason, $id_status, $duration_minutes]);
            $newId = $pdo->lastInsertId();

            // Registrar actividad
            $newData = [
                'id_patient' => $patient_id,
                'id_dentist_user' => $doctor_id,
                'appointment_date' => $appointment_date,
                'appointment_time' => $appointment_time,
                'reason' => $reason
            ];
            registerActivity($pdo, $currentUserId, 'INSERT', 'appointments', $newId, null, $newData);

            header("Location: ../index.php?tab=appointments&msg=created");
            exit;
        } catch (PDOException $e) {
            error_log("Error creating appointment: " . $e->getMessage());
            header("Location: ../index.php?tab=appointments&error=db");
            exit;
        }
    }

    // --- CERRAR CITA (crear historial y cambiar estado) ---
    if ($action == 'close_appointment') {
        $appointment_id = $data['appointment_id'] ?? 0;
        $diagnostic = $data['diagnostic'] ?? '';
        $treatment = $data['treatment'] ?? '';

        if (!$appointment_id || !$diagnostic || !$treatment) {
            http_response_code(400);
            echo json_encode(["error" => "Missing data"]);
            exit;
        }

        try {
            // Obtener estado anterior
            $oldStmt = $pdo->prepare("SELECT id_appointment_status FROM appointments WHERE id_appointment = ?");
            $oldStmt->execute([$appointment_id]);
            $oldStatus = $oldStmt->fetchColumn();

            // Cambiar estado a "Attended" (ajusta el ID según tu tabla appointment_statuses)
            $newStatus = 3;
            $update = $pdo->prepare("UPDATE appointments SET id_appointment_status = ? WHERE id_appointment = ?");
            $update->execute([$newStatus, $appointment_id]);

            // Obtener id_patient
            $patStmt = $pdo->prepare("SELECT id_patient FROM appointments WHERE id_appointment = ?");
            $patStmt->execute([$appointment_id]);
            $id_patient = $patStmt->fetchColumn();

            // Insertar historial médico
            $histSql = "INSERT INTO medical_histories (id_patient, id_appointment, diagnosis, treatment) VALUES (?, ?, ?, ?)";
            $histStmt = $pdo->prepare($histSql);
            $histStmt->execute([$id_patient, $appointment_id, $diagnostic, $treatment]);
            $historyId = $pdo->lastInsertId();

            // Registrar actividad (cambio de estado)
            registerActivity($pdo, $currentUserId, 'UPDATE', 'appointments', $appointment_id, 
                ['id_appointment_status' => $oldStatus], ['id_appointment_status' => $newStatus]);

            // Registrar actividad (creación de historial)
            registerActivity($pdo, $currentUserId, 'INSERT', 'medical_histories', $historyId, null, 
                ['diagnosis' => $diagnostic, 'treatment' => $treatment]);

            header("Location: ../index.php?tab=appointments&msg=closed");
            exit;
        } catch (PDOException $e) {
            error_log("Error closing appointment: " . $e->getMessage());
            header("Location: ../index.php?tab=appointments&error=close");
            exit;
        }
    }
}

// Si no se reconoce la acción
http_response_code(400);
echo json_encode(["error" => "Invalid action"]);
?>