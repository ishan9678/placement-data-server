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
        $stmtUser = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
            exit;
        }

        // Get query parameters
        $department = isset($_GET['department']) ? $_GET['department'] : null;
        $batch = isset($_GET['batch']) ? $_GET['batch'] : null;

        // Build the SQL query dynamically based on parameters
        $query = "SELECT * FROM students WHERE 1=1"; // Start with a valid condition
        $params = [];

        if ($batch !== null) {
            $query .= " AND batch = :batch";
            $params[':batch'] = $batch;
        }

        if ($department !== null) {
            $query .= " AND department = :department";
            $params[':department'] = $department;
        }

        $stmtStudents = $conn->prepare($query);
        $stmtStudents->execute($params);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('status' => 'success', 'students' => $students));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Database error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}

// Close the database connection
$conn = null;
