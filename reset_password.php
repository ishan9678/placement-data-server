<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $token = $data->token;
    $newPassword = $data->newPassword;

    $token_hash = hash("sha256", $token);

    try {
        // Check if the token exists in the database and is not expired
        $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ?");
        $stmt->execute([$token_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($user && strtotime($user['reset_token_expires_at']) > time()) {
            // Update the password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmtUpdate = $conn->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
            $stmtUpdate->execute([$hashedPassword, $user['id']]);

            // Get the current server time
            $currentServerTime = date("Y-m-d H:i:s");

            // Send the response with the current server time
            echo json_encode(array('status' => 'success', 'message' => 'Password reset successfully', 'currentServerTime' => $currentServerTime));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid or expired token'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
