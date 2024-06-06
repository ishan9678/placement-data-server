<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

try {
    $batch = isset($_GET['batch']) ? $_GET['batch'] : '';

    $stmtFacultyAdvisors = $conn->query("SELECT DISTINCT facultyAdvisorName FROM students where batch = '$batch'");
    $facultyAdvisors = $stmtFacultyAdvisors->fetchAll(PDO::FETCH_ASSOC);

    // Categories to include in the report
    $categories = ['Marquee', 'Super Dream', 'Dream', 'Day Sharing', 'Internship'];

    // Array to store consolidated report
    $consolidatedReport = [];

    foreach ($facultyAdvisors as $advisor) {

        $facultyAdvisorName = $advisor['facultyAdvisorName'];

        // Fetch section for the faculty advisor

        $stmtFacultyAdvisorSection = $conn->query("SELECT section FROM users WHERE name = '$facultyAdvisorName'  ");
        $facultyAdvisorSectionResult = $stmtFacultyAdvisorSection->fetch(PDO::FETCH_ASSOC);
        $facultyAdvisorSection = $facultyAdvisorSectionResult ? $facultyAdvisorSectionResult['section'] : null;


        // Count for total students
        $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ? ");
        $stmtTotalCount->execute([$facultyAdvisorName]);
        $totalCount = $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];


        // Count for Superset Enrolled
        $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled' ");
        $stmtSupersetCount->execute([$facultyAdvisorName]);
        $supersetCount = $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

        // Count for each category
        $categoryCounts = [];

        foreach ($categories as $category) {
            $stmtCategoryCount = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND category = ? ");
            $stmtCategoryCount->execute([$facultyAdvisorName, $category]);
            $categoryCount = $stmtCategoryCount->fetch(PDO::FETCH_ASSOC)['categoryCount'];

            // Store the category count
            $categoryCounts[$category] = $categoryCount;
        }

        // Calculate total offers
        $totalOffers = (int)$categoryCounts['Marquee'] + (int)$categoryCounts['Super Dream'] + (int)$categoryCounts['Dream'] + (int)$categoryCounts['Day Sharing'] + (int)$categoryCounts['Internship'];

        $uniqueCount = 0;

        $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ?");
        $stmtUniqueCount->execute([$facultyAdvisorName]);
        $uniqueCount += $stmtUniqueCount->fetch(PDO::FETCH_ASSOC)['unique_registerNumber_count'];

        // Consolidated report for this faculty advisor
        $consolidatedReport[] = [
            'facultyAdvisorName' => $facultyAdvisorName,
            'facultyAdvisorSection' => $facultyAdvisorSection, // Directly assign the section value
            'totalCount' => $totalCount,
            'supersetEnrolledCount' => $supersetCount,
            'marquee' => $categoryCounts['Marquee'],
            'superDream' => $categoryCounts['Super Dream'], // Ensure this matches the JSON format
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
