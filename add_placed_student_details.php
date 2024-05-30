<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Get data from the POST request
$data = json_decode(file_get_contents("php://input"), true);

// Check if data is an array
if (is_array($data)) {
    try {
        foreach ($data as $student) {
            // Sample data structure, adjust based on your form fields
            $registerNumber = $student['registerNumber'];
            $fullName = $student['fullName'];
            $section = $student['section'];
            $companyName = $student['companyName'];
            $category = $student['category'];
            $package = $student['package'];
            $facultyAdvisorName = $student['facultyAdvisorName'];
            $batch = $student['batch'];
            $specialization = $student['specialization'];


            // Check if the student exists in the students table
            $checkStudentQuery = $conn->prepare("SELECT * FROM students WHERE registerNumber = ?");
            $checkStudentQuery->execute([$registerNumber]);
            $existingStudent = $checkStudentQuery->fetch(PDO::FETCH_ASSOC);

            // If the student doesn't exist, add them to the students table
            if (!$existingStudent) {
                $insertStudentQuery = $conn->prepare("INSERT INTO students (registerNumber, name, section, batch, specialization, careerOption, facultyAdvisorName) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insertStudentQuery->execute([$registerNumber, $fullName, $section, $batch, $specialization, 'Superset Enrolled', $facultyAdvisorName]);
            }

            // Insert placed student details into the placed_students table
            $insertQuery = $conn->prepare("INSERT INTO placed_students (registerNumber, fullName, section, companyName, category, package, facultyAdvisor, batch, specialization) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insertQuery->execute([$registerNumber, $fullName, $section, $companyName, $category, $package, $facultyAdvisorName, $batch, $specialization]);
        }

        echo json_encode(array('status' => 'success', 'message' => 'Placed student details added successfully'));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid input data format'));
}

// Close the database connection
$conn = null;
