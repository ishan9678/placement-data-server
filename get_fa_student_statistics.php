<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000, https://placementdata.in/');
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
$stmt_fa_name = $conn->prepare($query_fa_name);
$stmt_fa_name->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_fa_name->execute();
$fa_name = $stmt_fa_name->fetchColumn();

// Initialize variables
$totalStudents = 0;
$supersetEnrolled = 0;
$higherStudies = 0;
$entrepreneurship = 0;
$arrears = 0;

$query = 'SELECT * FROM students WHERE facultyAdvisorName = :fa_name';
$stmt = $conn->prepare($query);
$stmt->bindParam(':fa_name', $fa_name, PDO::PARAM_STR);
$stmt->execute();

// Fetch data from the database
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $totalStudents++;

    switch ($row['careerOption']) {
        case 'Superset Enrolled':
            $supersetEnrolled++;
            break;
        case 'Higher Studies':
            $higherStudies++;
            break;
        case 'Entrepreneur':
            $entrepreneurship++;
            break;
        case 'Arrear/Detained':
            $arrears++;
            break;
    }
}

// Calculate the number of not eligible students for placements
$notEligible = $higherStudies + $entrepreneurship + $arrears;

// Prepare data to be sent to React
$data = array(
    'Number of Students Enrolled for Placement in Superset' => $supersetEnrolled,
    'Number of Students opted for Higher Studies' => $higherStudies,
    'Number of Students opted for Entrepreneurship' => $entrepreneurship,
    'Number of Students not eligible for Placement due to Arrears' => $arrears,
    'Number of Not Eligible Students for Placements' => $notEligible,
    'Total Number of Students' => $totalStudents,
);

// Send data to React in JSON format
echo json_encode($data);
