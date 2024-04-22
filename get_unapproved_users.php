<?php

require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000, https://placementdata.in/');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT id, name, employee_id, role, specialization, batch, email_id FROM users WHERE isapproved = 0");
    $stmt->execute();
    $unapprovedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array('status' => 'success', 'unapprovedUsers' => $unapprovedUsers));
} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}
