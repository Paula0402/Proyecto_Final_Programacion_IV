<?php
header("Content-Type: application/json");
require_once "../config/db.php";

// obtener filtros (pacientes o dentistas)
if (isset($_GET['action']) && $_GET['action'] === 'filters') {
    $type = $_GET['type'] ?? '';
    if ($type === 'patients') {
        // Pacientes que tienen al menos un historial médico
        $stmt = $pdo->query("
            SELECT p.id_patient, CONCAT(p.first_name, ' ', p.last_name) AS full_name
            FROM patients p
            WHERE p.active = 1
              AND EXISTS (SELECT 1 FROM medical_histories mh WHERE mh.id_patient = p.id_patient)
            ORDER BY p.first_name
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } elseif ($type === 'dentists') {
        // Dentistas (users con id_role=2) que tienen al menos un historial médico
        $stmt = $pdo->query("
            SELECT u.id_user, u.full_name
            FROM users u
            WHERE u.id_role = 2 AND u.active = 1
              AND EXISTS (
                  SELECT 1 FROM appointments a
                  INNER JOIN medical_histories mh ON a.id_appointment = mh.id_appointment
                  WHERE a.id_dentist_user = u.id_user
              )
            ORDER BY u.full_name
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid type. Use 'patients' or 'dentists'"]);
    }
    exit;
}

// Parámetros de consulta para el historial
$filter = $_GET['filter'] ?? 'all';      // all, patient, dentist
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$date_from = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
$date_to   = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Validación de fechas
if ($date_from && $date_to && $date_from > $date_to) {
    http_response_code(400);
    echo json_encode(["error" => "Date 'from' cannot be greater than date 'to'"]);
    exit;
}

// Mapeo de filtro a campos de la consulta
$patient_id = null;
$dentist_id = null;
if ($filter === 'patient' && $id) {
    $patient_id = $id;
} elseif ($filter === 'dentist' && $id) {
    $dentist_id = $id;
}

try {
    // Consulta principal
    $sql = "SELECT mh.id_history, mh.diagnosis, mh.treatment, mh.notes, 
                   mh.requires_control, mh.next_control_date,
                   CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
                   a.appointment_date, a.appointment_time,
                   u.full_name AS dentist_name
            FROM medical_histories mh
            INNER JOIN patients p ON mh.id_patient = p.id_patient
            INNER JOIN appointments a ON mh.id_appointment = a.id_appointment
            INNER JOIN users u ON a.id_dentist_user = u.id_user
            WHERE 1=1";
    $params = [];

    if ($patient_id) {
        $sql .= " AND mh.id_patient = :patient_id";
        $params[':patient_id'] = $patient_id;
    }
    if ($dentist_id) {
        $sql .= " AND a.id_dentist_user = :dentist_id";
        $params[':dentist_id'] = $dentist_id;
    }
    if ($date_from) {
        $sql .= " AND a.appointment_date >= :date_from";
        $params[':date_from'] = $date_from;
    }
    if ($date_to) {
        $sql .= " AND a.appointment_date <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Conteo total para paginación
    $countSql = "SELECT COUNT(*) FROM medical_histories mh
                 INNER JOIN appointments a ON mh.id_appointment = a.id_appointment
                 WHERE 1=1";
    if ($patient_id)  $countSql .= " AND mh.id_patient = :patient_id";
    if ($dentist_id)  $countSql .= " AND a.id_dentist_user = :dentist_id";
    if ($date_from)   $countSql .= " AND a.appointment_date >= :date_from";
    if ($date_to)     $countSql .= " AND a.appointment_date <= :date_to";

    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();

    echo json_encode(["data" => $data, "total" => $total]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>