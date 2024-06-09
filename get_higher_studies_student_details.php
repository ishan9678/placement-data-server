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

        // Check if a faculty advisor name is provided as a query parameter
        $facultyAdvisor = isset($_GET['advisor']) ? $_GET['advisor'] : null;

        $department = isset($_GET['department']) ? $_GET['department'] : null;

        $batch = isset($_GET['batch']) ? $_GET['batch'] : null;

        if ($facultyAdvisor && $department == null) {
            // Query students where facultyAdvisorName matches and careerOption is "Higher Studies"
            $stmtStudents = $conn->prepare("
                SELECT * FROM students 
                WHERE facultyAdvisorName = :facultyAdvisor 
                AND careerOption = 'Higher Studies'
                AND batch = :batch
            ");
            $stmtStudents->bindParam(':facultyAdvisor', $facultyAdvisor);
            $stmtStudents->bindParam(':batch', $batch);
            $stmtStudents->execute();
            $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(array('status' => 'success', 'students' => $students));
        } else if ($facultyAdvisor && $department) {
            // Query students where facultyAdvisorName matches and careerOption is "Higher Studies" and department matches
            $stmtStudents = $conn->prepare("
                SELECT * FROM students 
                WHERE facultyAdvisorName = :facultyAdvisor 
                AND careerOption = 'Higher Studies'
                AND department = :department
                AND batch = :batch
            ");
            $stmtStudents->bindParam(':facultyAdvisor', $facultyAdvisor);
            $stmtStudents->bindParam(':department', $department);
            $stmtStudents->bindParam(':batch', $batch);
            $stmtStudents->execute();
            $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(array('status' => 'success', 'students' => $students));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Faculty advisor name is required'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}

// Close the database connection
$conn = null;
