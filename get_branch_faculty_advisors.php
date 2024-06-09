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
        // Fetch the specialization of the logged-in user
        $stmtUser = $conn->prepare("SELECT specialization FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
            exit;
        }

        $batch = isset($_GET['batch']) ? $_GET['batch'] : null;

        // Fetch faculty advisors id with the same specialization
        $stmtAdvisorsID = $conn->prepare("SELECT employee_id FROM facultyadvisor_assignments WHERE (specialization = ? OR additional_specialization = ?) AND batch = ?");
        $stmtAdvisorsID->execute([$user['specialization'], $user['specialization'], $batch]);
        $facultyAdvisorsID = $stmtAdvisorsID->fetchAll(PDO::FETCH_ASSOC);

        $facultyAdvisors = array();
        foreach ($facultyAdvisorsID as $advisor) {
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
