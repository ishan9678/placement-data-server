<?php
require_once('./database/connect.php');

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

$batch = isset($_GET['batch']) ? $_GET['batch'] : 2025;

// Fetch the specialization for the user from the users table
$query_specialization = "SELECT specialization FROM users WHERE id = :user_id";
$stmt_specialization = $conn->prepare($query_specialization);
$stmt_specialization->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_specialization->execute();
$specialization = $stmt_specialization->fetchColumn();

// Prepare the SQL query with specialization filter
$query = "SELECT
    SUM(CASE WHEN package < 5 THEN 1 ELSE 0 END) AS 'Less than 5 lakhs',
    SUM(CASE WHEN package >= 5 AND package < 10 THEN 1 ELSE 0 END) AS '5 lakhs to 9.99 lakhs',
    SUM(CASE WHEN package >= 10 AND package < 15 THEN 1 ELSE 0 END) AS '10 lakhs to 14.99 lakhs',
    SUM(CASE WHEN package >= 15 AND package < 20 THEN 1 ELSE 0 END) AS '15 lakhs to 19.99 lakhs',
    SUM(CASE WHEN package >= 20 AND package <= 40 THEN 1 ELSE 0 END) AS '20 lakhs to 40 lakhs',
    SUM(CASE WHEN package > 40 THEN 1 ELSE 0 END) AS 'Greater than 40 lakhs',
    COUNT(*) AS 'TOTAL'
FROM placed_students
WHERE specialization = '$specialization'
    AND batch = '$batch'
    AND package > 0
    AND category != 'Internship'";


try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $data = [
        'Less than 5 lakhs' => (int)$row['Less than 5 lakhs'],
        '5 lakhs to 9.99 lakhs' => (int)$row['5 lakhs to 9.99 lakhs'],
        '10 lakhs to 14.99 lakhs' => (int)$row['10 lakhs to 14.99 lakhs'],
        '15 lakhs to 19.99 lakhs' => (int)$row['15 lakhs to 19.99 lakhs'],
        '20 lakhs to 40 lakhs' => (int)$row['20 lakhs to 40 lakhs'],
        'Greater than 40 lakhs' => (int)$row['Greater than 40 lakhs'],
    ];

    // Convert the data to JSON format
    $json_data = json_encode($data);

    // Output the JSON data
    header('Content-Type: application/json');
    echo $json_data;
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
