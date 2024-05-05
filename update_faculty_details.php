<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$response = array();

if (isset($data["employee_id"])) {

    $name = $data["name"];
    $employee_id = $data["employee_id"];
    $role = $data["role"];
    $specialization = $data["specialization"];
    $batch = $data["batch"];
    $email_id = $data['email_id'];
    $additional_specialization = $data['additional_specialization'];

    $faculties_sql = "UPDATE users SET name='$name', employee_id='$employee_id', role='$role', specialization='$specialization', batch='$batch', email_id='$email_id', additional_specialization='$additional_specialization' WHERE employee_id ='$employee_id'";

    if ($conn->query($faculties_sql) === TRUE) {
        $response['message'] = "Faculty record updated successfully";
    } else {
        $response['message'] = "Error updating Faculty record:";
    }
} else {
    $response['message'] = "Error: Invalid input data format. 'employee_id' key not found.";
}

echo json_encode($response);
