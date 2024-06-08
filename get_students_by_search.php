<?php
require_once('./database/connect.php');

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
$department = isset($_GET['department']) ? $_GET['department'] : "";

try {
    // Fetch student details from the students table based on the register number
    if ($department === "") {
        $stmt = $conn->prepare("SELECT registerNumber, name, section, department, specialization, batch, careerOption, facultyAdvisorName FROM students WHERE registerNumber = ?");
        $stmt->execute([$registerNumber]);
    } else {
        $stmt = $conn->prepare("SELECT registerNumber, name, section, department, specialization, batch, careerOption, facultyAdvisorName FROM students WHERE registerNumber = ? AND department = ?");
        $stmt->execute([$registerNumber, $department]);
    }
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(array('status' => 'error', 'message' => 'Student not found'));
        exit;
    }

    // Fetch placement details from the placed_students table based on the register number
    $stmtPlacement = $conn->prepare("SELECT id, companyName, category, package FROM placed_students WHERE registerNumber = ?");
    $stmtPlacement->execute([$registerNumber]);
    $placements = $stmtPlacement->fetchAll(PDO::FETCH_ASSOC);

    // If there are no entries in placed_students, set placements to an empty array
    if (!$placements) {
        $placements = array();
    }

    // Merge the student details and placement details
    $result = array('student' => $student, 'placements' => $placements);

    echo json_encode(array('status' => 'success', 'data' => $result));
} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}

// Close the database connection
$conn = null;
