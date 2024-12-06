<?php
$host = "localhost";
$username = "r1";
$password = "";
$database = "expenses_revenue";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>