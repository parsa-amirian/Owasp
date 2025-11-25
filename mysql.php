<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// MySQL connection parameters
$host = '192.168.1.101';
$username = 'world';
$password = '1234';
$database = 'world';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//echo "Connected successfully to MySQL!<br><br>";



// Close connection (optional, PHP will close it automatically at script end)
//$conn->close();



//ALTER TABLE users
//MODIFY COLUMN profile_picture VARCHAR(255)
//NOT NULL DEFAULT 'default.png'
//AFTER reset_token;


?>