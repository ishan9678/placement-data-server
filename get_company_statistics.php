<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$department = isset($_GET['department']) ? $_GET['department'] : "";
$batch = isset($_GET['batch']) ? $_GET['batch'] : "";

try {
    // Fetch all distinct specializations
    $stmtSpecializations = $conn->prepare("SELECT DISTINCT specialization FROM placed_students WHERE department = :department AND batch = :batch");
    $stmtSpecializations->bindParam(':department', $department, PDO::PARAM_STR);
    $stmtSpecializations->bindParam(':batch', $batch, PDO::PARAM_STR);
    $stmtSpecializations->execute();
    $specializations = $stmtSpecializations->fetchAll(PDO::FETCH_COLUMN);

    // Prepare the SQL statement to fetch company details and student count per specialization
    $stmtCompanies = $conn->prepare("
        SELECT 
            companyName, 
            category, 
            specialization, 
            COUNT(*) as numStudents 
        FROM placed_students 
        WHERE department = :department 
        AND batch = :batch
        GROUP BY companyName, category, specialization
    ");

    // Bind the department parameter to the query
    $stmtCompanies->bindParam(':department', $department, PDO::PARAM_STR);
    $stmtCompanies->bindParam(':batch', $batch, PDO::PARAM_STR);

    // Execute the query
    $stmtCompanies->execute();

    // Fetch the result
    $companies = $stmtCompanies->fetchAll(PDO::FETCH_ASSOC);

    // Initialize an array to store the final result
    $result = [];

    // Process the result to group by company and category
    foreach ($companies as $row) {
        $companyName = $row['companyName'];
        $category = $row['category'];
        $specialization = $row['specialization'];
        $numStudents = $row['numStudents'];

        if (!isset($result[$companyName])) {
            $result[$companyName] = [
                'category' => $category,
                'specializations' => array_fill_keys($specializations, '0') // Initialize all specializations with '0'
            ];
        }

        $result[$companyName]['specializations'][$specialization] = $numStudents;
    }

    // Output the result as JSON
    echo json_encode(['status' => 'success', 'company-statistics' => $result]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
