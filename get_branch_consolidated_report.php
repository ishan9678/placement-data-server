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

        // Fetch faculty advisor names from the same specialization
        $stmtFacultyAdvisors = $conn->prepare("SELECT DISTINCT name as facultyAdvisorName FROM users WHERE role = 'Faculty Advisor' AND (specialization = ? OR additional_specialization = ?) and batch = ?");
        $stmtFacultyAdvisors->execute([$user['specialization'], $user['specialization'], $batch]);
        $facultyAdvisors = $stmtFacultyAdvisors->fetchAll(PDO::FETCH_ASSOC);

        // Categories to include in the report
        $categories = ['Marquee', 'Super Dream', 'Dream', 'Day Sharing', 'Internship'];

        // Array to store consolidated report
        $consolidatedReport = [];

        $uniqueCount = 0; // Initialize outside the loop

        foreach ($facultyAdvisors as $advisor) {
            $facultyAdvisorName = $advisor['facultyAdvisorName'];
            $specialization = $user['specialization'];

            $stmtFacultyAdvisorSection = $conn->prepare("SELECT section FROM users WHERE name = ?");
            $stmtFacultyAdvisorSection->execute([$facultyAdvisorName]);
            $facultyAdvisorSection = $stmtFacultyAdvisorSection->fetch(PDO::FETCH_ASSOC)['section'];

            // Count for total students
            $stmtTotalCount = $conn->prepare("SELECT COUNT(*) as totalStudents FROM students WHERE facultyAdvisorName = ? AND specialization = ?");
            $stmtTotalCount->execute([$facultyAdvisorName, $specialization]);
            $totalCount = $stmtTotalCount->fetch(PDO::FETCH_ASSOC)['totalStudents'];

            // Count for Superset Enrolled
            $stmtSupersetCount = $conn->prepare("SELECT COUNT(*) as supersetEnrolledCount FROM students WHERE facultyAdvisorName = ? AND careerOption = 'Superset Enrolled' and specialization = ?");
            $stmtSupersetCount->execute([$facultyAdvisorName, $specialization]);
            $supersetCount = $stmtSupersetCount->fetch(PDO::FETCH_ASSOC)['supersetEnrolledCount'];

            // Count for each category
            $categoryCounts = [];

            foreach ($categories as $category) {
                $stmtCategoryCounts = $conn->prepare("SELECT category, COUNT(*) as categoryCount FROM placed_students WHERE facultyAdvisor = ? AND specialization = ? AND category = ?");
                $stmtCategoryCounts->execute([$facultyAdvisorName, $specialization, $category]);
                $categoryCount = $stmtCategoryCounts->fetch(PDO::FETCH_ASSOC)['categoryCount'];

                // Store the category count
                $categoryCounts[$category] = $categoryCount;
            }

            // Calculate total offers
            $totalOffers = (int)$categoryCounts['Marquee'] + (int)$categoryCounts['Super Dream'] + (int)$categoryCounts['Dream'] + (int)$categoryCounts['Day Sharing'] + (int)$categoryCounts['Internship'];

            $uniqueCount = 0;
            // fetch unique count
            $stmtUniqueCount = $conn->prepare("SELECT COUNT(DISTINCT registerNumber) AS unique_registerNumber_count FROM placed_students WHERE facultyAdvisor = ?");
            $stmtUniqueCount->execute([$facultyAdvisorName]);
            $uniqueCount += $stmtUniqueCount->fetch(PDO::FETCH_ASSOC)['unique_registerNumber_count'];

            // Consolidated report for this faculty advisor
            $consolidatedReport[] = [
                'facultyAdvisorName' => $facultyAdvisorName,
                'facultyAdvisorSection' => $facultyAdvisorSection, // Directly assign the section value
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