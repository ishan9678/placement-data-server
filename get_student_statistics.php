<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$batch = isset($_GET['batch']) ? $_GET['batch'] : 2025;

// Initialize variables
$totalStudents = 0;
$supersetEnrolled = 0;
$higherStudies = 0;
$entrepreneurship = 0;
$arrears = 0;

// Prepare the SQL query
$query = "SELECT * FROM students where batch = '$batch' ";
$stmt = $conn->prepare($query);
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
    'Number of Students not opted for Placements' => $notEligible,
    'Total Number of Students' => $totalStudents,
);

// Send data to React in JSON format
echo json_encode($data);
