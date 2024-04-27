<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid data format. Expected an array.'));
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO students (registerNumber, name, section, specialization, batch, careerOption, facultyAdvisorName) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($data as $student) {
            $registerNumber = $student["registerNumber"];
            $name = $student["name"];
            $section = $student["section"];
            $specialization = $student["specialization"];
            $batch  = $student["batch"];
            $careerOption  = $student["careerOption"];
            $facultyAdvisorName = $student["facultyAdvisorName"];

            $stmt->execute([$registerNumber, $name, $section, $specialization, $batch, $careerOption, $facultyAdvisorName]);
        }

        // Return JSON response for successful insertion
        echo json_encode(array('status' => 'success', 'message' => 'Student details added successfully!'));
    } catch (PDOException $e) {
        // Return JSON response for database error
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
