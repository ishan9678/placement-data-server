<?php
require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Check if the user is logged in by checking the session variable
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];


    try {
        // Fetch the empid of the faculty advisor
        $stmtUser = $conn->prepare("SELECT employee_id FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
            exit;
        }

        // Fetch all batch from facultyadvisor_assignments table with employee_id
        $stmtAssignments = $conn->prepare("SELECT batch, section, specialization FROM facultyadvisor_assignments WHERE employee_id = ?");
        $stmtAssignments->execute([$user['employee_id']]);
        $facultyAdvisorAssignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);


        echo json_encode(array('status' => 'success', 'assignments' => $facultyAdvisorAssignments));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}

// Close the database connection
$conn = null;
