<?php
session_start();
require_once('config/db_connect.php'); // connect to DB

// Redirect if not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}
$show_success = false;
$appointment_id = 0;
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $show_success = true;
    $appointment_id = intval($_GET['appointment_id']);
}

// Get patient details from session
$patient_id = $_SESSION['user_id'];
$patient_name = $_SESSION['full_name'];

// Prefill doctor if provided via GET (optional)
$prefill_doctor = isset($_GET['prefill_doctor']) ? $_GET['prefill_doctor'] : '';

// Fetch doctors for dropdown
$doctors_query = "SELECT user_id, full_name, email FROM users WHERE role='doctor'";
$doctors_result = mysqli_query($conn, $doctors_query);

// Fetch Lab Reports
$lab_reports_query = "SELECT * FROM lab_reports WHERE patient_id='$patient_id' ORDER BY report_date DESC";
$lab_reports_result = mysqli_query($conn, $lab_reports_query);

// Fetch Appointments
$appointments_query = "SELECT a.*, d.full_name AS doctor_name
                       FROM appointments a
                       JOIN users d ON a.doctor_id = d.user_id
                       WHERE a.patient_id='$patient_id'
                       ORDER BY a.appointment_date DESC";
$appointments_result = mysqli_query($conn, $appointments_query);

// Fetch Messages
$messages_query = "SELECT m.*, d.full_name AS sender_name
                   FROM messages m
                   JOIN users d ON m.sender_id = d.user_id
                   WHERE m.receiver_id='$patient_id'
                   ORDER BY m.sent_at DESC";
$messages_result = mysqli_query($conn, $messages_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Dashboard | Medicare Plus</title>
<style>
:root {
    --primary: #6a1b9a;
    --primary-dark: #4a0072;
    --primary-light: #9c4dcc;
    --bg: #f4e9ff;
    --card-bg: rgba(255, 255, 255, 0.7);
    --accent: #8e24aa;
    --danger: #d32f2f;
    --text-dark: #3a275f;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

body {
    background: linear-gradient(to bottom right, #f4e9ff, #f2d7ff);
    color: var(--text-dark);
}

/* Header */
header {
    background: linear-gradient(90deg, var(--primary-dark), var(--primary));
    color: #fff;
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

header h2 {
    font-weight: 600;
    font-size: 22px;
}

nav a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 6px;
    background: rgba(255,255,255,0.15);
    transition: 0.3s ease;
    margin-left: 10px;
}

nav a:hover {
    background: rgba(255,255,255,0.3);
}

/* Container */
.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

/* Glass Cards */
.card {
    background: var(--card-bg);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 30px rgba(0,0,0,0.15);
}

.card h1,
.card h2,
.card h3 {
    color: var(--primary-dark);
    margin-bottom: 12px;
}

/* Buttons */
button, .btn {
    background: linear-gradient(45deg, var(--accent), var(--primary));
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s ease;
}

button:hover, .btn:hover {
    opacity: 0.85;
    transform: translateY(-2px);
}

.btn.delete-btn {
    background: #d32f2f;
    background: linear-gradient(45deg, #d32f2f, #9a0007);
}

.btn.delete-btn:hover {
    opacity: 0.9;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: rgba(255, 255, 255, 0.6);
    border-radius: 10px;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

th, td {
    padding: 12px 15px;
    text-align: left;
}

th {
    background: linear-gradient(90deg, var(--primary-dark), var(--primary));
    color: #fff;
    font-weight: 600;
}

tr:nth-child(even) {
    background: rgba(240, 225, 255, 0.7);
}

tr:hover {
    background: rgba(230, 210, 255, 0.9);
}

/* Inputs */
input, select, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1.5px solid #d7b8ff;
    outline: none;
    margin-top: 5px;
    margin-bottom: 15px;
    background: rgba(255,255,255,0.85);
}

input:focus, select:focus, textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 5px rgba(155, 89, 182, 0.4);
}

/* Responsive */
@media(max-width:768px) {
    header {
        flex-direction: column;
        align-items: flex-start;
    }
    nav {
        margin-top: 12px;
    }
    table, th, td {
        font-size: 14px;
    }
}

</style>
</head>
<body>

<header>
    <h2>Welcome, <?php echo htmlspecialchars($patient_name); ?></h2>
    <nav>
        <a href="#lab-reports">Lab Reports</a>
        <a href="#appointments">Appointments</a>
        <a href="#messages">Messages</a>
        <a href="login.php">Logout</a>
    </nav>
</header>

<div class="container">

<!-- Lab Reports Section -->
<div class="card">
<h1 id="lab-reports">🧾 Your Lab Reports</h1>
<?php if(mysqli_num_rows($lab_reports_result) > 0): ?>
<table>
<tr><th>ID</th><th>Test Type</th><th>Report Date</th><th>Results</th><th>Doctor Comments</th><th>File</th></tr>
<?php while($row = mysqli_fetch_assoc($lab_reports_result)): ?>
<tr>
    <td><?php echo $row['report_id']; ?></td>
    <td><?php echo htmlspecialchars($row['test_type']); ?></td>
    <td><?php echo $row['report_date']; ?></td>
    <td><?php echo htmlspecialchars($row['results']); ?></td>
    <td><?php echo htmlspecialchars($row['doctor_comments']); ?></td>
    <td>
      <?php if(!empty($row['file_path'])): ?>
    <a class="btn" href="<?php echo htmlspecialchars($row['file_path']); ?>" download>Download</a>
<?php else: ?>No file<?php endif; ?>

    </td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No lab reports found.</p>
<?php endif; ?>
</div>



<!-- Book Appointment Form -->
<div class="card">
<h1>📅 Book a New Appointment</h1>
<form method="POST" action="book_appointment.php">
    <label for="doctor_id">Choose Doctor</label>
    <select name="doctor_id" id="doctor_id" required>
        <?php
        mysqli_data_seek($doctors_result, 0);
        while ($doctor = mysqli_fetch_assoc($doctors_result)) {
            $selected = ($doctor['user_id'] == $prefill_doctor) ? 'selected' : '';
            echo '<option value="'.$doctor['user_id'].'" '.$selected.'>'.htmlspecialchars($doctor['full_name']).'</option>';
        }
        ?>
    </select>

    <label for="appointment_date">Date</label>
    <input type="date" name="appointment_date" id="appointment_date" required>

    <label for="appointment_time">Time</label>
    <input type="time" name="appointment_time" id="appointment_time" required>

    <label for="notes">Notes (optional)</label>
    <textarea name="notes" id="notes" rows="3"></textarea>

    <button type="submit" class="btn">Book Appointment</button>
</form>
</div>
<?php if($show_success): ?>
<div style="padding:15px; margin-bottom:20px; border-radius:8px; background:#d1c4e9; color:#4a0072; font-weight:500;">
    ✅ Your appointment has been booked successfully!
    <a href="payment.php?appointment_id=<?php echo $appointment_id; ?>" style="color:#6a1b9a; text-decoration:underline; font-weight:600;">Proceed to Payment</a>
</div>
<?php endif; ?>

<!-- Appointments Section -->
<div class="card">
<h1 id="appointments">📅 Your Appointments</h1>
<?php if(mysqli_num_rows($appointments_result) > 0): ?>
<table>
<tr><th>ID</th><th>Doctor</th><th>Date</th><th>Time</th><th>Notes</th><th>Action</th></tr>
<?php while($appt = mysqli_fetch_assoc($appointments_result)): ?>
<tr>
<td><?php echo $appt['appointment_id']; ?></td>
<td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
<td><?php echo $appt['appointment_date']; ?></td>
<td><?php echo $appt['appointment_time']; ?></td>
<td><?php echo htmlspecialchars($appt['notes']); ?></td>
<td>
    <a class="btn delete-btn" href="delete_appointment.php?id=<?php echo $appt['appointment_id']; ?>" onclick="return confirm('Are you sure you want to delete this appointment?');">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No appointments booked.</p>
<?php endif; ?>
</div>

<!-- Contact Doctors Section -->
<div class="card">
<h2>👨‍⚕️ Contact a Doctor</h2>
<table>
<tr><th>Doctor</th><th>Email</th><th>Action</th></tr>
<?php
mysqli_data_seek($doctors_result, 0);
while ($doc = mysqli_fetch_assoc($doctors_result)) { ?>
<tr>
<td><?php echo htmlspecialchars($doc['full_name']); ?></td>
<td><?php echo htmlspecialchars($doc['email']); ?></td>
<td>
    <form method="POST" action="send_message.php" style="display:inline-block; margin-bottom:5px;">
        <input type="hidden" name="doctor_id" value="<?php echo $doc['user_id']; ?>">
        <!--<input type="text" name="message_text" placeholder="Type your message..." required style="width:200px; padding:5px; margin-right:5px;">-->
        <button type="submit" class="btn">Text Your Doctor</button>
    </form>
    <a class="btn" href="give_feedback.php?doctor_id=<?php echo $doc['user_id']; ?>" style="margin-left:5px;">Give Feedback</a>
</td>
</tr>
<?php } ?>
</table>
</div>

<!-- Messages Section -->
<div class="card">
<h1 id="messages">📨 Messages from Doctors</h1>
<?php if(mysqli_num_rows($messages_result) > 0): ?>
<table>
<tr><th>From</th><th>Message</th><th>Sent At</th></tr>
<?php while($msg = mysqli_fetch_assoc($messages_result)): ?>
<tr>
<td><?php echo htmlspecialchars($msg['sender_name']); ?></td>
<td><?php echo htmlspecialchars($msg['message']); ?></td>
<td><?php echo $msg['sent_at']; ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No messages yet.</p>
<?php endif; ?>
</div>

</div>
</body>
</html>
