<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$response = array();

// Check if the "students" key exists and is an array
if (isset($data["registerNumber"])) {
    // Process a single student
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


    // Update the student details in the students table
    $students_sql = "UPDATE students SET name='$name', section='$section', department='$department', specialization='$specialization', batch='$batch', careerOption='$careerOption', facultyAdvisorName='$facultyAdvisor' WHERE registerNumber='$registerNumber'";

    // Update or insert into the placed_students table
    if (!empty($companyName) && !empty($category) && !empty($package)) {
        $placed_students_sql = "UPDATE placed_students SET fullName='$name', section='$section', companyName='$companyName', category='$category', package='$package', facultyAdvisor='$facultyAdvisor', batch='$batch', department='$department', specialization = '$specialization' WHERE registerNumber = '$registerNumber' ";
    }

    // $placed_students_sql = "INSERT INTO placed_students (registerNumber, fullName, companyName, category, package, facultyAdvisor, batch, specialization)
    //     VALUES ('$registerNumber', '$name', '$companyName', '$category', '$package', '$facultyAdvisor', '$batch', '$specialization')
    //     ON DUPLICATE KEY UPDATE fullName='$name', companyName='$companyName', category='$category', package='$package', facultyAdvisor='$facultyAdvisor', batch='$batch', specialization='$specialization'";

    if ($conn->query($students_sql) === TRUE) {
        $response['message'] = "Student record updated successfully";
    } else {
        $response['message'] = "Error updating student record:";
    }

    if (!empty($placed_students_sql)) {

        if ($conn->query($placed_students_sql) === TRUE) {
            $response['message'] .= " Placed student record updated successfully";
        } else {
            $response['message'] .= " Error updating placed student record: ";
        }
    }
} else {
    $response['message'] = "Error: Invalid input data format. 'registerNumber' key not found.";
}

echo json_encode($response);
