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

$batch = isset($_GET['batch']) ? $_GET['batch'] : '';

// Fetch the specialization for the user from the users table
$query_specialization = "SELECT specialization FROM users WHERE id = :user_id";
$stmt_specialization = $conn->prepare($query_specialization);
$stmt_specialization->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_specialization->execute();
$specialization = $stmt_specialization->fetchColumn();

// Initialize variables
$totalStudents = 0;
$supersetEnrolled = 0;
$higherStudies = 0;
$entrepreneurship = 0;
$arrears = 0;

// Prepare the SQL query with specialization filter
$query = "SELECT * FROM students WHERE specialization = :specialization and batch = '$batch' ";
$stmt = $conn->prepare($query);
$stmt->bindParam(':specialization', $specialization, PDO::PARAM_STR);
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
    'Specialization' => $specialization,
    'Number of Students Enrolled for Placement in Superset' => $supersetEnrolled,
    'Number of Students opted for Higher Studies' => $higherStudies,
    'Number of Students opted for Entrepreneurship' => $entrepreneurship,
    'Number of Students not eligible for Placement due to Arrears' => $arrears,
    'Number of Students not opted for Placements' => $notEligible,
    'Total Number of Students' => $totalStudents,
);

// Send data to React in JSON format
echo json_encode($data);
