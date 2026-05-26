<?php
session_start();
require_once('config/db_connect.php'); 

// 1️⃣ GET appointment ID
if (!isset($_GET['appointment_id'])) {
    die("Error: Appointment ID missing!");
}
$appointment_id = intval($_GET['appointment_id']);

// 2️⃣ Get appointment details
$sql = "SELECT * FROM appointments WHERE appointment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Appointment not found!");
}
$appointment = $result->fetch_assoc();

$success = false;
$amount = 5000; 

if (isset($_POST['pay'])) {

    $user_id = $appointment['patient_id'];

    // 1️⃣ Get the real patient_id
    $getPatient = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
    $getPatient->bind_param("i", $user_id);
    $getPatient->execute();
    $patientRes = $getPatient->get_result();

    if ($patientRes->num_rows === 0) {
        die("Error: No patient record found for this user!");
    }

    $patientRow = $patientRes->fetch_assoc();
    $patient_id = $patientRow['patient_id'];

    // 2️⃣ Get card info
    $card_number = trim($_POST['card_number']);
    $expiry = trim($_POST['expiry']);
    $cvv = trim($_POST['cvv']);
    $card_last4 = substr($card_number, -4);
    $payment_method = 'card'; // ✅ define payment method

    // 3️⃣ Insert payment
    $stmt = $conn->prepare("
        INSERT INTO payments (appointment_id, patient_id, amount, payment_method, card_last4)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiss", $appointment_id, $patient_id, $amount, $payment_method, $card_last4);
    if ($stmt->execute()) {
        $success = true;

        // 4️⃣ Optional: update notes instead of status
        $stmt2 = $conn->prepare("UPDATE appointments SET notes = CONCAT(IFNULL(notes,''), ' | confirmed') WHERE appointment_id = ?");
        $stmt2->bind_param("i", $appointment_id);
        $stmt2->execute();
    } else {
        die("Payment failed: " . $stmt->error);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment - Medicare Plus</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f0f4f8;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        text-align: center;
        animation: fadeIn 1s ease forwards;
        width: 350px;
    }
    h2 { color: #2c3e50; margin-bottom: 20px; }
    p { color: #34495e; margin: 10px 0; }
    input {
        width: 100%;
        padding: 10px 12px;
        margin: 10px 0;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 16px;
    }
    button {
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        background: #27ae60;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    button:hover { background: #2ecc71; transform: translateY(-2px); }
    .success { color: #27ae60; font-weight: bold; font-size: 18px; margin-top: 20px; animation: fadeIn 1.5s ease forwards; }

    @keyframes fadeIn { 0% { opacity: 0; transform: translateY(20px);} 100% { opacity: 1; transform: translateY(0);} }
</style>
<?php if($success): ?>
<meta http-equiv="refresh" content="5;url=patient_dashboard.php">
<?php endif; ?>
</head>
<body>
<div class="container">
<?php if(!$success): ?>
    <h2>Appointment Payment</h2>
    <p>Doctor ID: <?= htmlspecialchars($appointment['doctor_id']) ?></p>
    <p>Date: <?= htmlspecialchars($appointment['appointment_date']) ?></p>
    <p>Time: <?= htmlspecialchars($appointment['appointment_time']) ?></p>
    <p>Amount: Rs. <?= $amount ?></p>

    <form method="POST">
        <input type="text" name="card_number" placeholder="Card Number" required maxlength="16">
        <input type="text" name="expiry" placeholder="MM/YY" required maxlength="5">
        <input type="text" name="cvv" placeholder="CVV" required maxlength="3">
        <button type="submit" name="pay">Pay Now</button>
    </form>
<?php else: ?>
    <div class="success">
        ✅ Booking Successful!<br>
        Your appointment with Doctor ID <?= htmlspecialchars($appointment['doctor_id']) ?> on <?= htmlspecialchars($appointment['appointment_date']) ?> at <?= htmlspecialchars($appointment['appointment_time']) ?> is confirmed.<br>
        Payment of Rs. <?= $amount ?> received.<br>
        Redirecting to your dashboard...
    </div>
<?php endif; ?>
</div>
</body>
</html>
