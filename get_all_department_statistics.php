<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

try {
    $batch = isset($_GET['batch']) ? $_GET['batch'] : '';

    // Array of specializations
    $specializations = ['AI', 'SWE', 'AI/ML'];

    // Array to store consolidated report
    $departmentStatistics = [];

    foreach ($specializations as $specialization) {

        // Fetch all faculty advisors for the specialization
        $stmtFacultyAdvisors = $conn->prepare("SELECT DISTINCT facultyAdvisorName FROM students WHERE specialization = ? AND batch = ?");
        $stmtFacultyAdvisors->execute([$specialization, $batch]);
        $facultyAdvisors = $stmtFacultyAdvisors->fetchAll(PDO::FETCH_ASSOC);

        // Categories to include in the report
        $categories = ['Marquee', 'Super Dream', 'Dream', 'Day Sharing', 'Internship'];

        // Initialize counts for the specialization
        $totalCount = 0;
        $supersetEnrolledCount = 0;
        $categoryCounts = array_fill_keys($categories, 0);
        $uniqueCount = 0;

        foreach ($facultyAdvisors as $advisor) {
            $facultyAdvisorName = $advisor['facultyAdvisorName'];

            // Count for total students
            $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ? AND specialization = ?  ");
            $stmtTotalCount->execute([$facultyAdvisorName, $specialization]);
            $totalCount += $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];

            // Count for Superset Enrolled
            $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled' AND specialization = ? ");
            $stmtSupersetCount->execute([$facultyAdvisorName, $specialization]);
            $supersetEnrolledCount += $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

            // Count for each category
            foreach ($categories as $category) {
                $stmtCategoryCount = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND category = ? AND specialization = ? ");
                $stmtCategoryCount->execute([$facultyAdvisorName, $category, $specialization]);
                $categoryCounts[$category] += $stmtCategoryCount->fetch(PDO::FETCH_ASSOC)['categoryCount'];
            }

            $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ?  AND specialization = ?");
            $stmtUniqueCount->execute([$facultyAdvisorName, $specialization]);
            $uniqueCount += $stmtUniqueCount->fetch(PDO::FETCH_ASSOC)['unique_registerNumber_count'];
        }

        // Calculate total offers
        $totalOffers = array_sum($categoryCounts);

        // Add the counts to the consolidated report
        $departmentStatistics[$specialization] = [
            'totalCount' => $totalCount,
            'supersetEnrolledCount' => $supersetEnrolledCount,
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
    echo json_encode(['status' => 'success', 'departmentStatistics' => $departmentStatistics]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
