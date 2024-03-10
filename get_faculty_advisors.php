<?php
require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Check if the user is logged in by checking the session variable
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    try {
        // Fetch faculty advisors from the database
        $stmtAdvisors = $conn->prepare("SELECT id, name FROM users WHERE role = ?");
        $stmtAdvisors->execute(['Faculty Advisor']);
        $facultyAdvisors = $stmtAdvisors->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('status' => 'success', 'facultyAdvisors' => $facultyAdvisors));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}

// Close the database connection
$conn = null;
