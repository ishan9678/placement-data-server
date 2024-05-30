<?php
require_once('./database/connect.php');

session_start(); // Start the session

header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Send a success response
    http_response_code(200);
    echo json_encode(array("status" => "success", "message" => "Logout successful"));
    exit;
} else {
    // Send an error response if the user is not logged in
    http_response_code(401);
    echo json_encode(array("status" => "error", "message" => "You are not logged in"));
    exit;
}
