<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$batch = isset($_GET['batch']) ? $_GET['batch'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Query to get all relevant package values for the specified batch
$query = "
    SELECT 
        package, companyName, fullName
    FROM 
        placed_students
    WHERE 
        batch = :batch
        AND department = :department
        AND package > 0
        AND category != 'Internship'
    ORDER BY package
";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bindParam(':batch', $batch);
$stmt->bindParam(':department', $department);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($packages) == 0) {
    echo json_encode(['error' => 'No data available']);
    exit;
}

// Calculate the max, min, and median packages
$package_values = array_column($packages, 'package');
$max_package = max($package_values);
$min_package = min($package_values);
$avg_package = array_sum($package_values) / count($package_values);


// Sort the packages to find the median
sort($package_values);
$mid = floor(count($package_values) / 2);

if (count($package_values) % 2 == 0) {
    $median_package = $package_values[$mid - 1];  // Choosing the lower middle value in case of even number of elements
} else {
    $median_package = $package_values[$mid];
}

// Initialize variables for company names and names
$max_company = '';
$min_company = '';
$median_company = '';
$max_name = '';
$min_name = '';
$median_name = '';

// Find the companies and names associated with max, min, and median values
foreach ($packages as $package) {
    if ($package['package'] == $max_package) {
        $max_company = $package['companyName'];
        $max_name = $package['fullName'];
    }
    if ($package['package'] == $min_package) {
        $min_company = $package['companyName'];
        $min_name = $package['fullName'];
    }
    if ($package['package'] == $median_package) {
        $median_company = $package['companyName'];
        $median_name = $package['fullName'];
    }
}

// Prepare the result
$result = [
    'max_package' => $max_package,
    'max_company' => $max_company,
    'max_name' => $max_name,
    'min_package' => $min_package,
    'min_company' => $min_company,
    'min_name' => $min_name,
    'median_package' => $median_package,
    'median_company' => $median_company,
    'median_name' => $median_name,
    'avg_package' => $avg_package
];

// Send the result to React in JSON format
echo json_encode($result);
