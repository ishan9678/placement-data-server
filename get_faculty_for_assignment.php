<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Check if the employee ID is provided in the URL parameter
if (!isset($_GET['employee_id'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Employee ID not provided'));
    exit;
}

$employee_id = $_GET['employee_id'];

try {
    // Fetch data from the users table
    $stmtUser = $conn->prepare("SELECT name, employee_id, email_id, role, department FROM users WHERE employee_id = ? AND role = 'Faculty Advisor'");
    $stmtUser->execute([$employee_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(array('status' => 'error', 'message' => 'User not found'));
        exit;
    }

    // Add the user's data to the result
    $result = [];
    array_push($result, $user);

    // Return the combined data
    echo json_encode(array('status' => 'success', 'faculty' => $result));
} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}

// Close the database connection
$conn = null;
