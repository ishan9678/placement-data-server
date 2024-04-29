<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$batch = isset($_GET['batch']) ? $_GET['batch'] : 2025;

// Query to get the highest, lowest, and average package values for the specified batch
$query = "
SELECT 
    MAX(package) AS max_package,
    MIN(package) AS min_package,
    ROUND(AVG(package), 2) AS avg_package
FROM 
    placed_students
WHERE
    batch = :batch
    AND package > 0
    AND category != 'Internship'
";


// Prepare and execute the query to get package statistics
$stmt = $conn->prepare($query);
$stmt->bindParam(':batch', $batch);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Get the company names for the highest and lowest package values
$max_package = $result['max_package'];
$min_package = $result['min_package'];

$query2 = "
    SELECT 
        companyName
    FROM 
        placed_students
    WHERE 
        (package = :max_package OR package = :min_package)
        AND batch = :batch
        AND package > 0
        AND category != 'Internship'
";

$stmt2 = $conn->prepare($query2);
$stmt2->bindParam(':max_package', $max_package);
$stmt2->bindParam(':min_package', $min_package);
$stmt2->bindParam(':batch', $batch);
$stmt2->execute();
$companies = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Add company names to the result array
$result['max_company'] = $companies[0]['companyName'];
$result['min_company'] = $companies[1]['companyName'];

// Send the result to React in JSON format
echo json_encode($result);
