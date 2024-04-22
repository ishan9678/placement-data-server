<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(file_get_contents('php://input'), true);

    // Retrieve form data
    $registerNumber = $data[0]["registerNumber"];
    $name = $data[0]["name"];
    $section = $data[0]["section"];
    $specialization = $data[0]["specialization"];
    $batch  = $data[0]["batch"];
    $careerOption  = $data[0]["careerOption"];
    $facultyAdvisorName = $data[0]["facultyAdvisorName"];



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
