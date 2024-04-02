<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

try {
    // Fetch all distinct company names from the database
    $stmtCompanies = $conn->prepare("SELECT DISTINCT companyName FROM placed_students");
    $stmtCompanies->execute();
    $companies = $stmtCompanies->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(array('status' => 'success', 'companies' => $companies));
} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}

// Close the database connection
$conn = null;
