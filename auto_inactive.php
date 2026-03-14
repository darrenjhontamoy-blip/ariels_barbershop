<?php
include 'config.php';

mysqli_query($conn,"
    UPDATE users
    SET status='inactive'
    WHERE role='barber'
    AND last_activity IS NOT NULL
    AND last_activity < (NOW() - INTERVAL 2 MINUTE)
");

