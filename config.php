<?php
// REPORT ALL ERRORS (helps debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "ariels_barbershop_db");

// Check connection
if ($conn->connect_error) {
    die("DB CONNECTION FAILED: " . $conn->connect_error);
}
