<?php
$servername = "localhost";  // or "127.0.0.1"
$username = "root";
$password = "";             // leave blank by default in XAMPP
$database = "medi_plus"; // must match the database name in phpMyAdmin

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
