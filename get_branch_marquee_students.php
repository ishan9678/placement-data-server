<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');


// Check if the user is logged in and get the user ID from the session

session_start(); // Start the session

// Check if the user is logged in and get the user ID from the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}


$user_id = $_SESSION['user_id'];

$batch = isset($_GET['batch']) ? $_GET['batch'] : 2025;

// Select the specialization from the users table using the user ID
$query = "SELECT specialization FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $specialization = $stmt->fetchColumn();

    // Select the marquee students based on the specialization
    $query = "SELECT registerNumber, fullName, companyName, package FROM placed_students WHERE specialization = :specialization and batch = '$batch' and category = 'Marquee' ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':specialization', $specialization, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert the data to JSON format
        $json_data = json_encode(['status' => 'success', 'marqueeStudents' => $data]);

        // Output the JSON data
        echo $json_data;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No marquee students found for the specialization']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User ID not found']);
}
