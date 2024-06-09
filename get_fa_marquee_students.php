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

$stmtFacultyAdvisor = $conn->prepare("SELECT name as facultyAdvisorName FROM users WHERE id = ?");
$stmtFacultyAdvisor->execute([$user_id]);
$facultyAdvisor = $stmtFacultyAdvisor->fetch(PDO::FETCH_ASSOC);

if (!$facultyAdvisor) {
    echo json_encode(array('status' => 'error', 'message' => 'Faculty advisor not found'));
    exit;
}

$facultyAdvisorName = $facultyAdvisor['facultyAdvisorName'];

$batch = isset($_GET['batch']) ? $_GET['batch'] : null;

// Select the marquee students for the faculty advisor
$query = "SELECT registerNumber, fullName, companyName, package FROM placed_students WHERE facultyAdvisor = ? AND category = 'Marquee' AND batch = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $facultyAdvisorName, PDO::PARAM_STR);
$stmt->bindParam(2, $batch, PDO::PARAM_STR);
$stmt->execute();


if ($stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert the data to JSON format
    $json_data = json_encode(['status' => 'success', 'marqueeStudents' => $data]);

    // Output the JSON data
    echo $json_data;
} else {
    echo json_encode(['status' => 'error', 'message' => 'No marquee students found for the faculty advisor']);
}
