<?php
require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    try {
        // Fetch faculty advisor name for the current user
        $stmtFacultyAdvisor = $conn->prepare("SELECT name as facultyAdvisorName FROM users WHERE id = ?");
        $stmtFacultyAdvisor->execute([$userId]);
        $facultyAdvisor = $stmtFacultyAdvisor->fetch(PDO::FETCH_ASSOC);

        if (!$facultyAdvisor) {
            echo json_encode(array('status' => 'error', 'message' => 'Faculty advisor not found'));
            exit;
        }

        // Fetch faculty advisor section
        $stmtFacultyAdvisorSection = $conn->prepare("SELECT section FROM users WHERE name = ?");
        $stmtFacultyAdvisorSection->execute([$facultyAdvisor['facultyAdvisorName']]);
        $facultyAdvisorSection = $stmtFacultyAdvisorSection->fetch(PDO::FETCH_ASSOC)['section'];

        // Categories to include in the report
        $categories = ['Marquee', 'Super Dream', 'Dream', 'Day Sharing', 'Internship'];

        // Array to store consolidated report
        $consolidatedReport = [];

        $uniqueCount = 0;

        // Count for total students
        $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ?");
        $stmtTotalCount->execute([$facultyAdvisor['facultyAdvisorName']]);
        $totalCount = $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];

        // Count for Superset Enrolled
        $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled'");
        $stmtSupersetCount->execute([$facultyAdvisor['facultyAdvisorName']]);
        $supersetCount = $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

        // Count for each category
        $categoryCounts = [];

        foreach ($categories as $category) {
            $stmtCategoryCount = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND category = ?");
            $stmtCategoryCount->execute([$facultyAdvisor['facultyAdvisorName'], $category]);
            $categoryCount = $stmtCategoryCount->fetch(PDO::FETCH_ASSOC)['categoryCount'];

            // Store the category count
            $categoryCounts[$category] = $categoryCount;
        }

        // Calculate total offers
        $totalOffers = (int)$categoryCounts['Marquee'] + (int)$categoryCounts['Super Dream'] + (int)$categoryCounts['Dream'] + (int)$categoryCounts['Day Sharing'] + (int)$categoryCounts['Internship'];

        $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ?");
        $stmtUniqueCount->execute([$facultyAdvisor['facultyAdvisorName']]); // Corrected parameter
        $uniqueCount = $stmtUniqueCount->fetch(PDO::FETCH_ASSOC)['unique_registerNumber_count'];


        // Consolidated report for this faculty advisor
        $consolidatedReport[] = [
            'facultyAdvisorName' => $facultyAdvisor['facultyAdvisorName'],
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
