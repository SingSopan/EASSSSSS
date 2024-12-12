<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "crowdfund";

// Create connection
$conn = new mysqli("localhost", "root", "", "crowdfund");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}