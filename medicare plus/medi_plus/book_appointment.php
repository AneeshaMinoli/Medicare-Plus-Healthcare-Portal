<?php
session_start();
require_once('config/db_connect.php'); // Connect to DB

// Redirect if not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $doctor_id = intval($_POST['doctor_id']);
    $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
    $appointment_time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    // Insert appointment into DB
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $notes);

    if ($stmt->execute()) {
        // Get the new appointment ID
        $appointment_id = $stmt->insert_id;

        // Redirect directly to payment page
        header("Location: payment.php?appointment_id={$appointment_id}");
        exit();
    } else {
        die("Error booking appointment: " . $stmt->error);
    }

   
}
$conn->close();
?>
