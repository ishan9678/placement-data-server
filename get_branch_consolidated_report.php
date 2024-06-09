<?php
require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    try {
        $batch = isset($_GET['batch']) ? $_GET['batch'] : "";
        // $department = isset($_GET['department']) ? $_GET['department'] : "";

        $stmtUser = $conn->prepare("SELECT specialization FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
            exit;
        }

        // Fetch faculty advisor names and sections from the same specialization
        $stmtFacultyAdvisorsID = $conn->prepare("SELECT employee_id, section FROM facultyadvisor_assignments WHERE (specialization = ? OR additional_specialization = ?) AND batch = ?");
        $stmtFacultyAdvisorsID->execute([$user['specialization'], $user['specialization'], $batch]);
        $facultyAdvisorsID = $stmtFacultyAdvisorsID->fetchAll(PDO::FETCH_ASSOC);

        $facultyAdvisors = [];
        foreach ($facultyAdvisorsID as $advisor) {
            $stmtUsers = $conn->prepare("SELECT name as facultyAdvisorName FROM users WHERE employee_id = ?");
            $stmtUsers->execute([$advisor['employee_id']]);
            $advisorData = $stmtUsers->fetch(PDO::FETCH_ASSOC);
            if ($advisorData) {
                $advisorData['section'] = $advisor['section'];
                $facultyAdvisors[] = $advisorData;
            }
        }

        // Categories to include in the report
        $categories = ['Marquee', 'Super Dream', 'Dream', 'Day Sharing', 'Internship'];

        // Array to store consolidated report
        $consolidatedReport = [];

        foreach ($facultyAdvisors as $advisor) {
            $facultyAdvisorName = $advisor['facultyAdvisorName'];
            $facultyAdvisorSection = $advisor['section'];
            $specialization = $user['specialization'];

            // Count for total students
            $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ? AND specialization = ? AND batch = ?");
            $stmtTotalCount->execute([$facultyAdvisorName, $specialization, $batch]);
            $totalCount = $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];

            // Count for Superset Enrolled
            $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled' AND specialization = ? AND batch = ?");
            $stmtSupersetCount->execute([$facultyAdvisorName, $specialization, $batch]);
            $supersetCount = $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

            // Count for each category
            $categoryCounts = [];
            foreach ($categories as $category) {
                $stmtCategoryCounts = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND specialization = ? AND category = ? AND batch = ?");
                $stmtCategoryCounts->execute([$facultyAdvisorName, $specialization, $category, $batch]);
                $categoryCounts[$category] = $stmtCategoryCounts->fetch(PDO::FETCH_ASSOC)['categoryCount'];
            }

            // Calculate total offers
            $totalOffers = array_sum($categoryCounts);

            // Fetch unique count
            $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ? AND specialization = ? AND batch = ?");
            $stmtUniqueCount->execute([$facultyAdvisorName, $specialization, $batch]);
            $uniqueCount = $stmtUniqueCount->fetch(PDO::FETCH_ASSOC)['unique_registerNumber_count'];

            // Consolidated report for this faculty advisor
            $consolidatedReport[] = [
                'facultyAdvisorName' => $facultyAdvisorName,
                'facultyAdvisorSection' => $facultyAdvisorSection,
                'supersetEnrolledCount' => $supersetCount,
                'totalCount' => $totalCount,
                'marquee' => $categoryCounts['Marquee'],
                'superDream' => $categoryCounts['Super Dream'],
                'dream' => $categoryCounts['Dream'],
                'daySharing' => $categoryCounts['Day Sharing'],
                'internship' => $categoryCounts['Internship'],
                'totalOffers' => $totalOffers,
                'uniqueCount' => $uniqueCount,
            ];
        }

        // Return the consolidated report
        echo json_encode(['status' => 'success', 'consolidatedReport' => $consolidatedReport]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}
