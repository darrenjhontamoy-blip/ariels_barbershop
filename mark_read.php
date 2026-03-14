<?php
include '../config.php';

mysqli_query($conn, "
    UPDATE notifications 
    SET status = 'read' 
    WHERE user_id = 1
");

header("Location: dashboard.php");
exit();