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
        // Fetch the name of the faculty advisor
        $stmtUser = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
            exit;
        }

        // Check if a faculty advisor name is provided as a query parameter
        $facultyAdvisor = isset($_GET['advisor']) ? $_GET['advisor'] : $user['name'];

        // Fetch placed students for the specified faculty advisor
        $stmtStudents = $conn->prepare("SELECT * FROM placed_students WHERE facultyAdvisor = ?");
        $stmtStudents->execute([$facultyAdvisor]);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('status' => 'success', 'facultyAdvisorName' => $facultyAdvisor, 'students' => $students));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}

// Close the database connection
$conn = null;
