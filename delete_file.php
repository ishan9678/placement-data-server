<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    // Retrieve registerNumber from the request body
    $registerNumber = $data["registerNumber"];
    $companyName = $data["companyName"];

    // Fetch the file path from the database based on the registerNumber
    $stmt = $conn->prepare("SELECT file FROM placed_students WHERE registerNumber = ? AND companyName = ?");
    $stmt->execute([$registerNumber, $companyName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        // File not found in the database
        echo json_encode(array('status' => 'error', 'message' => 'File not found'));
        exit();
    }

    $filePath = $result["file"];

    // Delete the file from the server
    if (($filePath)) {
        // If file deletion is successful, update the database to remove the file path
        $stmt = $conn->prepare("UPDATE placed_students SET file = NULL WHERE registerNumber = ? AND companyName = ?");
        $stmt->execute([$registerNumber, $companyName]);
        echo json_encode(array('status' => 'success', 'message' => 'File deleted successfully'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to delete file'));
    }
}
