<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id'])) exit;

$id = (int)$_POST['id'];
$user_id = $_SESSION['user_id'];

mysqli_query($conn,"DELETE FROM notifications WHERE id=$id AND user_id=$user_id");