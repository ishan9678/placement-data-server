<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$response = array();

// Check if the "registerNumber" key exists
if (isset($data["registerNumber"])) {
    // Process the student data
    $id = $data["id"];
    $registerNumber = $data["registerNumber"];
    $name = $data["name"];
    $section = $data["section"];
    $department = $data["department"];
    $specialization = $data["specialization"];
    $careerOption = $data["careerOption"];
    $facultyAdvisor = $data["facultyAdvisorName"];
    $batch = $data["batch"];
    $companyName = $data["companyName"];
    $category = $data["category"];
    $package = $data["package"];

    var_dump($data);

    try {
        // Update the student details in the students table
        $students_sql = "UPDATE students SET name = :name, section = :section, department = :department, specialization = :specialization, batch = :batch, careerOption = :careerOption, facultyAdvisorName = :facultyAdvisorName WHERE registerNumber = :registerNumber";
        $stmt = $conn->prepare($students_sql);

        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':section', $section);
        $stmt->bindValue(':department', $department);
        $stmt->bindValue(':specialization', $specialization);
        $stmt->bindValue(':batch', $batch);
        $stmt->bindValue(':careerOption', $careerOption);
        $stmt->bindValue(':facultyAdvisorName', $facultyAdvisor);
        $stmt->bindValue(':registerNumber', $registerNumber);

        if ($stmt->execute()) {
            $response['message'] = "Student record updated successfully";
        } else {
            $response['message'] = "Error updating student record: " . implode(", ", $stmt->errorInfo());
        }

        // Update or insert into the placed_students table if the required fields are provided
        if (!empty($companyName) && !empty($category) && !empty($package)) {
            $placed_students_sql = "UPDATE placed_students SET fullName = :fullName, section = :section, companyName = :companyName, category = :category, package = :package, facultyAdvisor = :facultyAdvisor, batch = :batch, department = :department, specialization = :specialization WHERE id = :id";
            $stmt = $conn->prepare($placed_students_sql);

            $stmt->bindValue(':fullName', $name);
            $stmt->bindValue(':section', $section);
            $stmt->bindValue(':companyName', $companyName);
            $stmt->bindValue(':category', $category);
            $stmt->bindValue(':package', $package);
            $stmt->bindValue(':facultyAdvisor', $facultyAdvisor);
            $stmt->bindValue(':batch', $batch);
            $stmt->bindValue(':department', $department);
            $stmt->bindValue(':specialization', $specialization);
            $stmt->bindValue(':id', $id);

            if ($stmt->execute()) {
                $response['message'] .= " Placed student record updated successfully";
            } else {
                $response['message'] .= " Error updating placed student record: " . implode(", ", $stmt->errorInfo());
            }
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
} else {
    $response['message'] = "Error: Invalid input data format. 'registerNumber' key not found.";
}

echo json_encode($response);
