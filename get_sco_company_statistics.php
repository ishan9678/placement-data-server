<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$batch = isset($_GET['batch']) ? $_GET['batch'] : "";

try {
    // Fetch all distinct specializations
    $stmtSpecializations = $conn->prepare("SELECT DISTINCT department FROM placed_students WHERE  batch = :batch");
    $stmtSpecializations->bindParam(':batch', $batch, PDO::PARAM_STR);
    $stmtSpecializations->execute();
    $departments = $stmtSpecializations->fetchAll(PDO::FETCH_COLUMN);

    // Prepare the SQL statement to fetch company details and student count per specialization
    $stmtCompanies = $conn->prepare("
        SELECT 
            companyName, 
            category, 
            department, 
            COUNT(*) as numStudents 
        FROM placed_students 
        WHERE batch = :batch
        GROUP BY companyName, category, department
    ");

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
        $department = $row['department'];
        $numStudents = $row['numStudents'];

        if (!isset($result[$companyName])) {
            $result[$companyName] = [
                'category' => $category,
                'departments' => array_fill_keys($departments, '0') // Initialize all specializations with '0'
            ];
        }

        $result[$companyName]['specializations'][$department] = $numStudents;
    }

    // Output the result as JSON
    echo json_encode(['status' => 'success', 'company-statistics' => $result]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
