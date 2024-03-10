<?php
require_once('./database/connect.php'); // Adjust the path based on your file structure

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Get data from the POST request
$data = json_decode(file_get_contents("php://input"), true);

// Sample data structure, adjust based on your form fields
$registerNumber = $data['registerNumber'];
$fullName = $data['fullName'];
$section = $data['section'];
$companyName = $data['companyName'];
$category = $data['category'];
$package = $data['package'];
$facultyAdvisorName = $data['facultyAdvisorName'];
$batch = $data['batch'];

try {
    // Check if the student exists in the students table
    $checkStudentQuery = $conn->prepare("SELECT * FROM students WHERE registerNumber = ?");
    $checkStudentQuery->execute([$registerNumber]);
    $existingStudent = $checkStudentQuery->fetch(PDO::FETCH_ASSOC);

    // If the student doesn't exist, add them to the students table
    if (!$existingStudent) {
        $insertStudentQuery = $conn->prepare("INSERT INTO students (registerNumber, name, section, batch, specialization, careerOption, facultyAdvisorName) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // If specialization is empty, set it to an empty string
        $specialization = isset($data['specialization']) ? $data['specialization'] : '';
        $insertStudentQuery->execute([$registerNumber, $fullName, $section, $batch, $specialization, 'Superset Enrolled', $facultyAdvisorName]);
    }

    // Insert placed student details into the placed_students table
    $insertQuery = $conn->prepare("INSERT INTO placed_students (registerNumber, fullName, section, companyName, category, package, facultyAdvisor, batch) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertQuery->execute([$registerNumber, $fullName, $section, $companyName, $category, $package, $facultyAdvisorName, $batch]);

    echo json_encode(array('status' => 'success', 'message' => 'Placed student details added successfully'));
} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}

// Close the database connection
$conn = null;
