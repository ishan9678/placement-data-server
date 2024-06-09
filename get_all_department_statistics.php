<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

try {
    $batch = isset($_GET['batch']) ? $_GET['batch'] : '';
    $department = isset($_GET['department']) ? $_GET['department'] : '';

    if (empty($batch) || empty($department)) {
        throw new Exception("Batch and Department are required.");
    }

    // Fetch specializations from the department
    $stmtSpecializations = $conn->prepare("SELECT DISTINCT specialization FROM students WHERE department = ? AND batch = ?");
    $stmtSpecializations->execute([$department, $batch]);
    $specializations = $stmtSpecializations->fetchAll(PDO::FETCH_ASSOC);

    // Check if specializations were found
    if (empty($specializations)) {
        throw new Exception("No specializations found for the given department and batch.");
    }

    // Array to store consolidated report
    $departmentStatistics = [];

    foreach ($specializations as $specRow) {
        $specialization = $specRow['specialization'];

        // Fetch all faculty advisors for the specialization
        $stmtFacultyAdvisors = $conn->prepare("SELECT DISTINCT facultyAdvisorName FROM students WHERE specialization = ? AND batch = ?");
        $stmtFacultyAdvisors->execute([$specialization, $batch]);
        $facultyAdvisors = $stmtFacultyAdvisors->fetchAll(PDO::FETCH_ASSOC);

        // Check if faculty advisors were found
        if (empty($facultyAdvisors)) {
            continue; // Skip this specialization if no faculty advisors found
        }

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
            $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ? AND specialization = ? AND batch = ?");
            $stmtTotalCount->execute([$facultyAdvisorName, $specialization, $batch]);
            $totalCount += $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];

            // Count for Superset Enrolled
            $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled' AND specialization = ? AND batch = ?");
            $stmtSupersetCount->execute([$facultyAdvisorName, $specialization, $batch]);
            $supersetEnrolledCount += $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

            // Count for each category
            foreach ($categories as $category) {
                $stmtCategoryCount = $conn->prepare("SELECT COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND category = ? AND specialization = ? AND batch = ?");
                $stmtCategoryCount->execute([$facultyAdvisorName, $category, $specialization, $batch]);
                $categoryCounts[$category] += $stmtCategoryCount->fetch(PDO::FETCH_ASSOC)['categoryCount'];
            }

            // Unique count of students placed
            $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ? AND specialization = ? AND batch = ?");
            $stmtUniqueCount->execute([$facultyAdvisorName, $specialization, $batch]);
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
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
