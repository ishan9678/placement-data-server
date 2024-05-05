<?php

require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Origin: http://localhost:3000, https://placementdata.in/');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data["userId"];

    try {
        $stmt = $conn->prepare("UPDATE users SET isapproved = 1 WHERE id = ?");
        $stmt->execute([$userId]);

        echo json_encode(array('status' => 'success', 'message' => 'User approved successfully'));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
