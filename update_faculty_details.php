<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$response = array();

if (isset($data["employee_id"]) && isset($data["update_type"])) {
    $employee_id = $data["employee_id"];
    $update_type = $data["update_type"];

    if ($update_type == "users") {
        $name = $data["name"];
        $role = $data["role"];
        $specialization = $data["specialization"];
        $batch = $data["batch"];
        $email_id = $data['email_id'];
        $additional_specialization = $data['additional_specialization'];

        try {
            $conn->beginTransaction();
            $sql = "UPDATE users SET name=?, role=?, specialization=?, batch=?, email_id=?, additional_specialization=? WHERE employee_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $role, $specialization, $batch, $email_id, $additional_specialization, $employee_id]);
            $conn->commit();
            $response['message'] = "User record updated successfully";
        } catch (PDOException $e) {
            $conn->rollBack();
            $response['message'] = "Error updating user record: " . $e->getMessage();
        }
    } elseif ($update_type == "facultyadvisor_assignments") {
        $name = $data["name"];
        $email_id = $data['email_id'];

        $specialization = $data["specialization"];
        $batch = $data["batch"];
        $section = $data['section'];
        $additional_specialization = $data['additional_specialization'];

        try {
            $conn->beginTransaction();
            $sql = "UPDATE facultyadvisor_assignments SET specialization=?, batch=?, section=?, additional_specialization=? WHERE employee_id=?";
            $sql2 = "UPDATE users SET name=?, email_id=? WHERE employee_id=?";
            $stmt = $conn->prepare($sql);
            $stmt2 = $conn->prepare($sql2);
            $stmt->execute([$specialization, $batch, $section, $additional_specialization, $employee_id]);
            $stmt2->execute([$name, $email_id, $employee_id]);
            $conn->commit();
            $response['message'] = "Faculty advisor assignment record updated successfully";
        } catch (PDOException $e) {
            $conn->rollBack();
            $response['message'] = "Error updating faculty advisor assignment record: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = "Invalid request";
}

echo json_encode($response);
