<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$response = array();

if (isset($data["employee_id"], $data["specialization"], $data["batch"], $data["section"])) {
    $employee_id = $data["employee_id"];
    $department = $data["department"];
    $specialization = $data["specialization"];
    $batch = $data["batch"];
    $section = $data['section'];

    try {
        $conn->beginTransaction();
        $sql = "INSERT INTO facultyadvisor_assignments (employee_id, department, specialization, batch, section) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employee_id, $department, $specialization, $batch, $section]);
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Faculty advisor assignment added successfully";
    } catch (PDOException $e) {
        $conn->rollBack();
        $response['success'] = false;
        $response['message'] = "Error adding faculty advisor assignment record: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request";
}

echo json_encode($response);
