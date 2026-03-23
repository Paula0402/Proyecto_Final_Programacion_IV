<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {

    $stmt = $pdo->query("
        SELECT s.*, p.full_name
        FROM sales s
        JOIN patients p ON s.patient_id = p.id_patient
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method == "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO sales
            (patient_id, total_amount)
            VALUES (:patient, :total)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":patient" => $data["patient_id"],
        ":total" => $data["total"]
    ]);

    echo json_encode(["message"=>"sale created"]);
}