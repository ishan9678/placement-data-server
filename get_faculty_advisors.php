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
    $department = isset($_GET['department']) ? $_GET['department'] : "";
    $batch = isset($_GET['batch']) ? $_GET['batch'] : "";

    try {
        // fetch employee ids of faculty advisors
        $stmtAdvisors = $conn->prepare("Select employee_id from facultyadvisor_assignments where department = ? and batch = ?");
        $stmtAdvisors->execute([$department, $batch]);
        $facultyAdvisorsEmployeeID = $stmtAdvisors->fetchAll(PDO::FETCH_ASSOC);

        // Fetch id and name from users table based on faculty advisors' employee ids
        $facultyAdvisors = array();
        foreach ($facultyAdvisorsEmployeeID as $advisor) {
            $stmtUsers = $conn->prepare("SELECT id, name FROM users WHERE employee_id = ?");
            $stmtUsers->execute([$advisor['employee_id']]);
            $advisorData = $stmtUsers->fetch(PDO::FETCH_ASSOC);
            $facultyAdvisors[] = $advisorData;
        }

        echo json_encode(array('status' => 'success', 'facultyAdvisors' => $facultyAdvisors));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}

// Close the database connection
$conn = null;
