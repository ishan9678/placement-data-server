<?php
require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');


// Check if the user is logged in by checking the session variable
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("SELECT name, role, department, specialization, section, batch FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(array('status' => 'success', 'name' => $user['name'], 'role' => $user['role'], 'department' => $user['department'], 'specialization' => $user['specialization'],  'section' => $user['section'], 'batch' => $user['batch']));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
}
