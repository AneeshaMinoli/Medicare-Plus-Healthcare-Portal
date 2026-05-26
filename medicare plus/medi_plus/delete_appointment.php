<?php
session_start();
require_once('config/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $appointment_id = mysqli_real_escape_string($conn, $_GET['id']);
    $patient_id = $_SESSION['user_id'];

    // Make sure the appointment belongs to the logged-in patient
    $check_sql = "SELECT * FROM appointments WHERE appointment_id='$appointment_id' AND patient_id='$patient_id'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $delete_sql = "DELETE FROM appointments WHERE appointment_id='$appointment_id'";
        mysqli_query($conn, $delete_sql);
        echo "<script>alert('Appointment deleted successfully!'); window.location='patient_dashboard.php';</script>";
    } else {
        echo "<script>alert('Unauthorized action.'); window.location='patient_dashboard.php';</script>";
    }
} else {
    header("Location: patient_dashboard.php");
    exit();
}
?>
