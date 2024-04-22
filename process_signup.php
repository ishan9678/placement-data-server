<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $employeeId = $_POST["employeeId"];
    $emailId = $_POST["emailId"];
    $role = $_POST["role"];
    $section = $_POST["section"];
    $specialization = $_POST["specialization"];
    $batch = $_POST["batch"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // Check if the email ID or employee ID already exists in the database
    try {
        $stmtCheck = $conn->prepare("SELECT * FROM users WHERE email_id = ? OR employee_id = ?");
        $stmtCheck->execute([$emailId, $employeeId]);
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            echo json_encode(array('status' => 'error', 'message' => 'Email ID or Employee ID already exists.'));
            exit;
        }

        // Prepare the INSERT statement based on the role
        if ($role == "Faculty Advisor") {
            $stmtUser = $conn->prepare("INSERT INTO users (name, employee_id, email_id, role, specialization, batch, section, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtUser->bindParam(1, $name);
            $stmtUser->bindParam(2, $employeeId);
            $stmtUser->bindParam(3, $emailId);
            $stmtUser->bindParam(4, $role);
            $stmtUser->bindParam(5, $specialization);
            $stmtUser->bindParam(6, $batch);
            $stmtUser->bindParam(7, $section);
            $stmtUser->bindParam(8, $password);
        } else {
            $stmtUser = $conn->prepare("INSERT INTO users (name, employee_id, email_id, role, specialization, batch, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtUser->bindParam(1, $name);
            $stmtUser->bindParam(2, $employeeId);
            $stmtUser->bindParam(3, $emailId);
            $stmtUser->bindParam(4, $role);
            $stmtUser->bindParam(5, $specialization);
            $stmtUser->bindParam(6, $batch);
            $stmtUser->bindParam(7, $password);
        }

        $stmtUser->execute();
        echo json_encode(array('status' => 'success', 'message' => 'Registration successful!'));
    } catch (PDOException $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
    }
}
