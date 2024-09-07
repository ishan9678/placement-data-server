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

try {
    // Fetch the name of the faculty advisor
    $query_fa_name = "SELECT name FROM users WHERE id = :user_id";
    $stmt_fa_name = $conn->prepare($query_fa_name);
    $stmt_fa_name->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_fa_name->execute();
    $fa_name = $stmt_fa_name->fetchColumn();

    if (!$fa_name) {
        echo json_encode(['status' => 'error', 'message' => 'Faculty advisor not found']);
        exit;
    }

    $batch = isset($_GET['batch']) ? $_GET['batch'] : null;

    // Query to get all relevant package values for the specified batch
    $query = "
        SELECT 
            package, companyName, fullName
        FROM 
            placed_students
        WHERE 
            facultyAdvisor = :fa_name
            AND package > 0
            AND category != 'Internship'
            AND batch = :batch
        ORDER BY package
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fa_name', $fa_name, PDO::PARAM_STR);
    $stmt->bindParam(':batch', $batch, PDO::PARAM_STR);
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($packages) == 0) {
        echo json_encode(['error' => 'No data available']);
        exit;
    }

    $unique_avg_query = "
    SELECT AVG(max_package) AS unique_avg_package
    FROM (
        SELECT MAX(package) AS max_package
        FROM placed_students
        WHERE package > 0 AND batch = :batch AND category != 'Internship'
        GROUP BY registerNumber
    ) AS max_packages;
";

    $stmt = $conn->prepare($unique_avg_query);
    $stmt->bindValue(':batch', $batch);
    $stmt->execute();
    $unique_avg_package = (float)$stmt->fetch(PDO::FETCH_ASSOC)['unique_avg_package'];

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
        'avg_package' => $avg_package,
        'unique_avg_package' => $unique_avg_package,
    ];

    // Send the result to React in JSON format
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

// Close the database connection
$conn = null;