<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

    try {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email_id = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update the reset_token_hash and reset_token_expires_at columns
            $stmtUpdate = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email_id = ?");
            $stmtUpdate->execute([$token_hash, $expiry, $email]);

            echo json_encode(array('status' => 'success', 'message' => 'Reset token generated successfully.'));

            $mail = require __DIR__ . "/mailer.php";

            $mail->setFrom("noreply@example.come");
            $mail->addAddress($email);
            $mail->Subject = "Password Reset";
            $mail->Body = <<<END
            Click <a href="https://placementdata.in/reset-password?token=$token">here</a> to reset your password.
        END;

            try {
                $mail->Send();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
            }
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Email not found.'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
