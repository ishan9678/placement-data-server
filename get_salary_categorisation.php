<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$batch = isset($_GET['batch']) ? $_GET['batch'] : 2025;

$query = "SELECT
    SUM(CASE WHEN package < 5 THEN 1 ELSE 0 END) AS 'Less than 5 lakhs',
    SUM(CASE WHEN package >= 5 AND package < 10 THEN 1 ELSE 0 END) AS '5 lakhs to 9.99 lakhs',
    SUM(CASE WHEN package >= 10 AND package < 15 THEN 1 ELSE 0 END) AS '10 lakhs to 14.99 lakhs',
    SUM(CASE WHEN package >= 15 AND package < 20 THEN 1 ELSE 0 END) AS '15 lakhs to 19.99 lakhs',
    SUM(CASE WHEN package >= 20 AND package <= 40 THEN 1 ELSE 0 END) AS '20 lakhs to 40 lakhs',
    SUM(CASE WHEN package > 40 THEN 1 ELSE 0 END) AS 'Greater than 40 lakhs',
    COUNT(*) AS 'TOTAL'
FROM placed_students
WHERE batch = '$batch'
GROUP BY batch";


try {
    $stmt = $conn->query($query);
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
