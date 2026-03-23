<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {

    $stmt = $pdo->query("
        SELECT a.*, p.full_name as patient, u.full_name as doctor
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id_patient
        JOIN users u ON a.assigned_user = u.id_user
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method == "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO appointments
            (patient_id, assigned_user, appointment_date)
            VALUES (:patient, :doctor, :date)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":patient" => $data["patient_id"],
        ":doctor" => $data["assigned_user"],
        ":date" => $data["appointment_date"]
    ]);

    echo json_encode(["message"=>"Appointment created successfully"]);
}