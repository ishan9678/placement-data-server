<?php
require_once('./database/connect.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


// Check if a file has been uploaded
if (isset($_FILES['file'])) {
    // The path to store the uploaded file
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);


    // Move the uploaded file to the target location
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "The file " . htmlspecialchars(basename($_FILES["file"]["name"])) . " has been uploaded.";

        // Prepare an SQL query to insert the file path into the database
        $stmt = $conn->prepare("UPDATE placed_students SET file=:file WHERE registerNumber=:registerNumber");
        $stmt->bindParam(':file', $target_file);
        $stmt->bindParam(':registerNumber', $_POST['registerNumber']);

        // Execute the query
        if ($stmt->execute()) {
            echo "File path stored successfully";
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$conn = null;
