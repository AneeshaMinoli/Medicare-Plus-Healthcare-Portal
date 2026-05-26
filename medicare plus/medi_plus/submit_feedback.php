<?php
session_start();
require_once('config/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit_feedback'])) {

    $patient_user_id = $_SESSION['user_id'];
    $doctor_id = (int) $_POST['doctor_id'];
    $rating = (int) $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // GET patient_id
    $pQuery = mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $patient_user_id LIMIT 1");
    $pData = mysqli_fetch_assoc($pQuery);
    $patient_id = $pData['patient_id'];

    // INSERT feedback
    $sql = "INSERT INTO feedbacks (doctor_id, patient_id, rating, comment, created_at)
            VALUES ($doctor_id, $patient_id, $rating, '$comment', NOW())";

    if (mysqli_query($conn, $sql)) {
        header("Location: give_feedback.php?success=1");
        exit();
    } else {
        header("Location: give_feedback.php?error=1");
        exit();
    }
}
?>
