<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(file_get_contents('php://input'), true);

    // Retrieve form data
    $registerNumber = $data["registerNumber"];
    $name = $data["name"];
    $section = $data["section"];
    $specialization = $data["specialization"];
    $batch  = $data["batch"];
    $careerOption  = $data["careerOption"];
    $facultyAdvisorName = $data["facultyAdvisorName"];

    echo ($registerNumber);

    error_reporting(E_ALL);
    ini_set('display_errors', 1);


    try {
        // Insert data into all_students table
        $stmt = $conn->prepare("INSERT INTO students (registerNumber, name, section, specialization, batch, careerOption, facultyAdvisorName) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$registerNumber, $name, $section, $specialization, $batch, $careerOption, $facultyAdvisorName]);

        // Return JSON response for successful insertion
        echo json_encode(array('status' => 'success', 'message' => 'Student details added successfully!'));
    } catch (PDOException $e) {
        // Return JSON response for database error
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
