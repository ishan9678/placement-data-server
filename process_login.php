<?php

require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeId = $_POST["employeeId"];
    $password = $_POST["password"];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE employee_id = ?");
        $stmt->execute([$employeeId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['temp_acc'] == 1) {
                $current_time = new DateTime();
                $expiry_time = new DateTime($user['temp_acc_expired_at']);
                if ($current_time < $expiry_time) {
                    $_SESSION['user_id'] = $user['id'];
                    echo json_encode(array('status' => 'success', 'message' => 'Login successful!'));
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Account validity expired'));
                }
            } else if ($user['isapproved'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                echo json_encode(array('status' => 'success', 'message' => 'Login successful!'));
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'User not approved'));
            }
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid credentials'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
