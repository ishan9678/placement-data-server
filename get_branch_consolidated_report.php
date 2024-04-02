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
        $stmtUser = $conn->prepare("SELECT specialization FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
            exit;
        }


        // Fetch faculty advisor names from the same specialization
        $stmtFacultyAdvisors = $conn->prepare("SELECT DISTINCT name as facultyAdvisorName FROM users WHERE role = 'Faculty Advisor' AND specialization = ?");
        $stmtFacultyAdvisors->execute([$user['specialization']]);
        $facultyAdvisors = $stmtFacultyAdvisors->fetchAll(PDO::FETCH_ASSOC);

        // Categories to include in the report
        $categories = ['marquee', 'super dream', 'dream', 'day sharing', 'internship'];

        // Array to store consolidated report
        $consolidatedReport = [];

        foreach ($facultyAdvisors as $advisor) {
            $facultyAdvisorName = $advisor['facultyAdvisorName'];

            // Count for Superset Enrolled
            $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled'");
            $stmtSupersetCount->execute([$facultyAdvisorName]);
            $supersetCount = $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

            // Count for each category
            $categoryCounts = [];

            foreach ($categories as $category) {
                $stmtCategoryCount = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND category = ?");
                $stmtCategoryCount->execute([$facultyAdvisorName, $category]);
                $categoryCount = $stmtCategoryCount->fetch(PDO::FETCH_ASSOC)['categoryCount'];

                // Store the category count
                $categoryCounts[$category] = $categoryCount;
            }

            // Calculate total offers
            // Calculate total offers
            $totalOffers = (int)$categoryCounts['marquee'] + (int)$categoryCounts['super dream'] + (int)$categoryCounts['dream'] + (int)$categoryCounts['day sharing'] + (int)$categoryCounts['internship'];



            // Consolidated report for this faculty advisor
            $consolidatedReport[] = [
                'facultyAdvisorName' => $facultyAdvisorName,
                'supersetEnrolledCount' => $supersetCount,
                'marquee' => $categoryCounts['marquee'],
                'superDream' => $categoryCounts['super dream'],
                'dream' => $categoryCounts['dream'],
                'daySharing' => $categoryCounts['day sharing'],
                'internship' => $categoryCounts['internship'],
                'totalOffers' => $totalOffers,
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
