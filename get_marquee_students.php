<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000, https://placementdata.in/');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$batch = isset($_GET['batch']) ? $_GET['batch'] : "";

$query = "SELECT registerNumber, fullName, companyName, package FROM placed_students WHERE category = 'marquee' and batch = '$batch' ";
$stmt = $conn->prepare($query);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert the data to JSON format
    $json_data = json_encode(['status' => 'success', 'marqueeStudents' => $data]);

    // Output the JSON data
    echo $json_data;
} else {
    echo json_encode(['status' => 'error', 'message' => 'No marquee students found']);
}
