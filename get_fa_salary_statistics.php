<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

session_start(); // Start the session

// Check if the user is logged in and get the user ID from the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$query_fa_name = "SELECT name FROM users WHERE id = :user_id";
$stmt_fa_name  = $conn->prepare($query_fa_name);
$stmt_fa_name->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_fa_name->execute();

if ($stmt_fa_name->rowCount() > 0) {
    $fa_name = $stmt_fa_name->fetchColumn();

    $query = "
        SELECT 
            MAX(package) AS max_package,
            MIN(package) AS min_package,
            ROUND(AVG(package), 2) AS avg_package
        FROM 
            placed_students
        WHERE 
            facultyAdvisor = :fa_name
            AND package > 0
            AND category != 'Internship'
    ";

    // Prepare and execute the query to get package statistics
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fa_name', $fa_name, PDO::PARAM_STR);
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
            AND package > 0
            AND category != 'Internship'
    ";

    $stmt2 = $conn->prepare($query2);
    $stmt2->bindParam(':max_package', $max_package);
    $stmt2->bindParam(':min_package', $min_package);
    $stmt2->execute();
    $companies = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Add company names to the result array
    $result['max_company'] = $companies[0]['companyName'];
    $result['min_company'] = $companies[1]['companyName'];

    // Send the result to React in JSON format
    echo json_encode($result);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User ID not found']);
}
