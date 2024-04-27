<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Check if the register number is provided in the URL parameter
if (!isset($_GET['registerNumber'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Register number not provided'));
    exit;
}

$registerNumber = $_GET['registerNumber'];

try {
    // Fetch student details from the students table based on the register number
    $stmt = $conn->prepare("SELECT registerNumber, name, section, specialization, batch,  careerOption, facultyAdvisorName FROM students WHERE registerNumber = ?");
    $stmt->execute([$registerNumber]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(array('status' => 'error', 'message' => 'Student not found'));
        exit;
    }

    // Fetch placement details from the placed_students table based on the register number
    $stmtPlacement = $conn->prepare("SELECT companyName, category, package FROM placed_students WHERE registerNumber = ?");
    $stmtPlacement->execute([$registerNumber]);
    $placement = $stmtPlacement->fetch(PDO::FETCH_ASSOC);

    // If there is no entry in placed_students, set placement fields to null
    if (!$placement) {
        $placement = array('companyName' => null, 'category' => null, 'package' => null);
    }

    // Merge the student and placement details
    $result = array_merge($student, $placement);

    echo json_encode(array('status' => 'success', 'student' => $result));
} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}

// Close the database connection
$conn = null;
