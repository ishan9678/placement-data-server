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

    $params = array();

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
        $facultyAdvisor = isset($_GET['advisor']) ? $_GET['advisor'] : null;

        $department = isset($_GET['department']) ? $_GET['department'] : "";

        $batch = isset($_GET['batch']) ? $_GET['batch'] : "";

        // Construct the SQL query
        $sql = "SELECT students.registerNumber, name, students.section, students.careerOption , facultyAdvisorName, students.batch FROM students 
                LEFT JOIN placed_students 
                ON students.registerNumber = placed_students.registerNumber
                WHERE placed_students.companyName IS NULL AND students.careerOption = 'Superset Enrolled'";

        // If a faculty advisor name is provided, add it to the query
        if ($facultyAdvisor !== null) {
            $sql .= " AND facultyAdvisorName = ?";
            $params[] = $facultyAdvisor;
        }

        if ($department !== "") {
            $sql .= " AND students.department = ?";
            $params[] = $department;
        }

        if ($batch !== "") {
            $sql .= " AND students.batch = ?";
            $params[] = $batch;
        }

        // Fetch unplaced students based on the constructed query
        $stmtStudents = $conn->prepare($sql);
        $stmtStudents->execute($params);
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
