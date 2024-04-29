<?php

require_once('./database/connect.php');

session_start();

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_id = $_POST["email"];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email_id = ?");
        $stmt->execute([$email_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // User exists
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(array('status' => 'success', 'message' => 'Login Successful', 'id' => $_SESSION['user_id']));
        } else {
            // User doesn't exist
            echo json_encode(array('status' => 'ok', 'message' => 'User not found', 'email' => $email_id, 'requiresAdditionalDetails' => true));
        }
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage(), 'email' => $email_id));
    }
}
