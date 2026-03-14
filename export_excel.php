<?php
session_start();
include '../config.php';

/* ==========================
   ADMIN ONLY ACCESS
========================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

/* ==========================
   CSV HEADERS FOR DOWNLOAD
========================== */
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=ariels_barbershop_report.csv");

$output = fopen("php://output", "w");

/* ==========================
   REPORT TITLE
========================== */
fputcsv($output, ['ARIELS BARBERSHOP - FULL REPORT']);
fputcsv($output, []);

/* ==========================
   CUSTOMERS SECTION
========================== */
fputcsv($output, ['CUSTOMERS']);
fputcsv($output, ['ID', 'Full Name', 'Username', 'Email', 'Status']);

$customersQuery = mysqli_query($conn, "
    SELECT id, fullname, username, email_address, status
    FROM users
    WHERE role = 'customer'
");

while ($row = mysqli_fetch_assoc($customersQuery)) {
    fputcsv($output, [
        $row['id'],
        $row['fullname'],
        $row['username'],
        $row['email_address'],
        $row['status']
    ]);
}

fputcsv($output, []);

/* ==========================
   BARBERS SECTION
========================== */
fputcsv($output, ['BARBERS']);
fputcsv($output, ['ID', 'Full Name', 'Username', 'Email', 'Status']);

$barbersQuery = mysqli_query($conn, "
    SELECT id, fullname, username, email_address, status
    FROM users
    WHERE role = 'barber'
");

while ($row = mysqli_fetch_assoc($barbersQuery)) {
    fputcsv($output, [
        $row['id'],
        $row['fullname'],
        $row['username'],
        $row['email_address'],
        $row['status']
    ]);
}

fputcsv($output, []);

/* ==========================
   APPOINTMENTS SECTION
========================== */
fputcsv($output, ['APPOINTMENTS']);
fputcsv($output, ['ID', 'Customer', 'Barber', 'Service', 'Date', 'Time', 'Status']);

$appointmentsQuery = mysqli_query($conn, "
    SELECT id, customer_name, barber_name, service, appointment_date, appointment_time, status
    FROM appointments
    ORDER BY appointment_date DESC, appointment_time DESC
");

while ($row = mysqli_fetch_assoc($appointmentsQuery)) {
    fputcsv($output, [
        $row['id'],
        $row['customer_name'],
        $row['barber_name'],
        $row['service'],
        $row['appointment_date'],
        $row['appointment_time'],
        $row['status']
    ]);
}

fputcsv($output, []);

/* ==========================
   PAYMENTS
========================== */
fputcsv($output, ['PAYMENTS']);
fputcsv($output, ['ID', 'Appointment ID', 'Customer', 'Amount', 'Status', 'Payment Date']);

$q = mysqli_query($conn, "
    SELECT 
        p.id,
        p.appointment_id,
        a.customer_name,
        a.price,
        p.payment_status,
        p.payment_date
    FROM payments p
    LEFT JOIN appointments a ON p.appointment_id = a.id
    ORDER BY p.payment_date DESC
");

while ($row = mysqli_fetch_assoc($q)) {
    fputcsv($output, [
        $row['id'],
        $row['appointment_id'],
        $row['customer_name'],
        $row['price'],
        $row['payment_status'],
        $row['payment_date'] ?? '-'
    ]);
}


fclose($output);
exit;
