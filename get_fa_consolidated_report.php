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
        $batch = isset($_GET['batch']) ? $_GET['batch'] : null;

        // Fetch employee id and name for the current user
        $stmtFacultyAdvisorID = $conn->prepare("SELECT employee_id, name FROM users WHERE id = ?");
        $stmtFacultyAdvisorID->execute([$userId]);
        $facultyAdvisorID = $stmtFacultyAdvisorID->fetch(PDO::FETCH_ASSOC);

        $employee_id = $facultyAdvisorID['employee_id'];
        $facultyAdvisorName = $facultyAdvisorID['name'];

        // Fetch faculty advisor section
        $stmtFacultyAdvisorSection = $conn->prepare("SELECT section FROM facultyadvisor_assignments WHERE employee_id = ? AND batch = ?");
        $stmtFacultyAdvisorSection->execute([$employee_id, $batch]);
        $facultyAdvisorSection = $stmtFacultyAdvisorSection->fetch(PDO::FETCH_ASSOC)['section'];

        // Categories to include in the report
        $categories = ['Marquee', 'Super Dream', 'Dream', 'Day Sharing', 'Internship'];

        // Array to store consolidated report
        $consolidatedReport = [];

        $uniqueCount = 0;

        // Count for total students
        $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ? AND batch = ?");
        $stmtTotalCount->execute([$facultyAdvisorName, $batch]);
        $totalCount = $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];

        // Count for Superset Enrolled
        $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled' AND batch = ?");
        $stmtSupersetCount->execute([$facultyAdvisorName, $batch]);
        $supersetCount = $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

        // Count for each category
        $categoryCounts = [];

        foreach ($categories as $category) {
            $stmtCategoryCount = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND category = ? AND batch = ?");
            $stmtCategoryCount->execute([$facultyAdvisorName, $category, $batch]);
            $categoryCount = $stmtCategoryCount->fetch(PDO::FETCH_ASSOC)['categoryCount'];

            // Store the category count
            $categoryCounts[$category] = $categoryCount;
        }

        // Calculate total offers
        $totalOffers = (int)$categoryCounts['Marquee'] + (int)$categoryCounts['Super Dream'] + (int)$categoryCounts['Dream'] + (int)$categoryCounts['Day Sharing'] + (int)$categoryCounts['Internship'];

        $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ? AND batch = ?");
        $stmtUniqueCount->execute([$facultyAdvisorName, $batch]);
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

        // Return the consolidated report
        echo json_encode(['status' => 'success', 'consolidatedReport' => $consolidatedReport]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}
