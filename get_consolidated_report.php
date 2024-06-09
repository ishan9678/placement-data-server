<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

try {
    $batch = isset($_GET['batch']) ? $_GET['batch'] : '';
    $department = isset($_GET['department']) ? $_GET['department'] : '';

    if (empty($batch) || empty($department)) {
        throw new Exception("Batch and Department are required");
    }

    $stmtFacultyAdvisorsID = $conn->prepare("SELECT employee_id FROM facultyadvisor_assignments WHERE batch = ? AND department = ?");
    $stmtFacultyAdvisorsID->execute([$batch, $department]);
    $facultyAdvisorsID = $stmtFacultyAdvisorsID->fetchAll(PDO::FETCH_ASSOC);


    // Categories to include in the report
    $categories = ['Marquee', 'Super Dream', 'Dream', 'Day Sharing', 'Internship'];

    // Array to store consolidated report
    $consolidatedReport = [];

    foreach ($facultyAdvisorsID as $advisorID) {
        $employee_id = $advisorID['employee_id'];

        $stmtFacultyAdvisorName = $conn->prepare("SELECT name FROM users WHERE employee_id = ?");
        $stmtFacultyAdvisorName->execute([$employee_id]);
        $facultyAdvisorName = $stmtFacultyAdvisorName->fetch(PDO::FETCH_ASSOC)['name'];

        // Fetch section for the faculty advisor
        $stmtFacultyAdvisorSection = $conn->prepare("SELECT section FROM facultyadvisor_assignments WHERE employee_id = ? AND batch = ? AND department = ?");
        $stmtFacultyAdvisorSection->execute([$employee_id, $batch, $department]);
        $facultyAdvisorSection = $stmtFacultyAdvisorSection->fetch(PDO::FETCH_ASSOC)['section'];

        // Count for total students
        $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ? AND batch = ? AND department = ?");
        $stmtTotalCount->execute([$facultyAdvisorName, $batch, $department]);
        $totalCount = $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];

        // Count for Superset Enrolled
        $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled' AND batch = ? AND department = ?");
        $stmtSupersetCount->execute([$facultyAdvisorName, $batch, $department]);
        $supersetCount = $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

        // Count for each category
        $categoryCounts = [];

        foreach ($categories as $category) {
            $stmtCategoryCount = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND category = ? AND batch = ? AND department = ?");
            $stmtCategoryCount->execute([$facultyAdvisorName, $category, $batch, $department]);
            $categoryCounts[$category] = $stmtCategoryCount->fetch(PDO::FETCH_ASSOC)['categoryCount'];
        }

        // Calculate total offers
        $totalOffers = array_sum($categoryCounts);

        // Unique count of students placed
        $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ? AND batch = ? AND department = ?");
        $stmtUniqueCount->execute([$facultyAdvisorName, $batch, $department]);
        $uniqueCount = $stmtUniqueCount->fetch(PDO::FETCH_ASSOC)['unique_registerNumber_count'];

        // Consolidated report for this faculty advisor
        $consolidatedReport[] = [
            'facultyAdvisorName' => $facultyAdvisorName,
            'facultyAdvisorSection' => $facultyAdvisorSection,
            'totalCount' => $totalCount,
            'supersetEnrolledCount' => $supersetCount,
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
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
