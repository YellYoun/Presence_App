<?php
// Database connection parameters
$host = 'localhost';
$db_name = 'attendance_management';
$username = 'root'; // Change as needed
$password = '';     // Change as needed

// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset('utf8mb4');
?>
