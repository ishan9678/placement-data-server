<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Check if the register number is provided in the URL parameter
if (!isset($_GET['employee_id'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Employee ID not provided'));
    exit;
}

$employee_id = $_GET['employee_id'];


try {
    $stmt = $conn->prepare("SELECT name, employee_id, role, specialization, batch,  email_id, section, additional_specialization FROM users WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faculty) {
        echo json_encode(array('status' => 'error', 'message' => 'Faculty not found'));
        exit;
    }


    echo json_encode(array('status' => 'success', 'faculty' => $faculty));
} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}

// Close the database connection
$conn = null;
