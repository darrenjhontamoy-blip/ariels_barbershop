<?php
include 'functions.php';

$test_number = "639354282671"; // Palitan ng number mo
$message = "Hello! This is a test from Ariel's Barbershop.";

$response = sendSMS($test_number, $message);
echo "Response from Semaphore: <br>";
echo $response;
?>
