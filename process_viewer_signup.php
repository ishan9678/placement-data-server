<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $employeeId = $_POST["employeeId"];
    $emailId = $_POST["emailId"];
    $department = $_POST["department"];
    $temp_acc_expired_at_str = $_POST["expirationDate"];

    // Remove the redundant timezone information from the input string
    $temp_acc_expired_at_str = preg_replace('/\(.*?\)/', '', $temp_acc_expired_at_str);

    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $temp_acc = 1;
    $isapproved = 1;

    // Convert the string to a DateTime object
    $temp_acc_expired_at = new DateTime($temp_acc_expired_at_str);

    // Check if the email ID or employee ID already exists in the database
    try {
        $stmtCheck = $conn->prepare("SELECT * FROM users WHERE email_id = ? OR employee_id = ?");
        $stmtCheck->execute([$emailId, $employeeId]);
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            echo json_encode(array('status' => 'error', 'message' => 'Email ID or Employee ID already exists.'));
            exit;
        }

        // Prepare the INSERT statement
        $stmtUser = $conn->prepare("INSERT INTO users (name, employee_id, email_id, department, temp_acc, temp_acc_expired_at, isapproved, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtUser->bindParam(1, $name);
        $stmtUser->bindParam(2, $employeeId);
        $stmtUser->bindParam(3, $emailId);
        $stmtUser->bindParam(4, $department);
        $stmtUser->bindParam(5, $temp_acc);
        $temp_acc_expired_at_formatted = $temp_acc_expired_at->format('Y-m-d H:i:s');
        $stmtUser->bindParam(6, $temp_acc_expired_at_formatted); // Use the formatted date variable
        $stmtUser->bindParam(7, $isapproved);
        $stmtUser->bindParam(8, $password);

        $stmtUser->execute();
        echo json_encode(array('status' => 'success', 'message' => 'Viewer Account Created Successfully'));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
