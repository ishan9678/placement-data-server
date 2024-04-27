<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// The registerNumber of the student
$registerNumber = $_GET['registerNumber'];

// Fetch the file path from the database
$stmt = $conn->prepare("SELECT file FROM placed_students WHERE registerNumber = ?");
$stmt->execute([$registerNumber]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if ($file) {
    // The path to the file
    $filePath = $file['file'];

    // Check if the file exists
    if (file_exists($filePath)) {
        // Set headers to prompt the client to download the file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // Output the file contents
        readfile($filePath);
        exit;
    }
}

// Close the database connection
$conn = null;
