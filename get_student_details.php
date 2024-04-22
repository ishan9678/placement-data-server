<?php
require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Origin: http://localhost:3000, https://placementdata.in/');
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

        // Fetch all students along with the faculty advisor name and the file path
        $stmtStudents = $conn->prepare("SELECT *, file FROM students WHERE facultyAdvisorName = ?");
        $stmtStudents->execute([$user['name']]);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('status' => 'success', 'facultyAdvisorName' => $user['name'], 'students' => $students));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}

// Close the database connection
$conn = null;
