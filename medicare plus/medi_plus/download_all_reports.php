<?php
session_start();
include 'config/db_connect.php';

// Only doctors can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    die("Access denied.");
}

$doctor_id = $_SESSION['user_id'];

// Fetch all report files for this doctor
$query = "SELECT file_path FROM lab_reports WHERE doctor_id = '$doctor_id' AND file_path IS NOT NULL";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("No report files found to download.");
}

// Create a ZIP file
$zip = new ZipArchive();
$zip_name = "lab_reports_" . date('Ymd_His') . ".zip";

if ($zip->open($zip_name, ZipArchive::CREATE) !== TRUE) {
    die("Could not create ZIP file.");
}

while ($row = mysqli_fetch_assoc($result)) {
    $file = $row['file_path'];
    if (file_exists($file)) {
        $zip->addFile($file, basename($file));
    }
}

$zip->close();

// Send ZIP to browser
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_name . '"');
header('Content-Length: ' . filesize($zip_name));
readfile($zip_name);

// Delete temporary ZIP from server
unlink($zip_name);
exit();
