<?php
session_start();
require_once('config/db_connect.php');

// Only allow doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['full_name'] ?? 'Doctor';
$success = "";

// Handle reply form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'], $_POST['patient_id'])) {
    $patient_id = intval($_POST['patient_id']);
    $reply_message = trim($_POST['reply_message']);

    if (!empty($reply_message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $doctor_id, $patient_id, $reply_message);
        $stmt->execute();
        $stmt->close();
        $success = "✅ Reply sent successfully!";
    }
}

// Fetch all messages where doctor is sender or receiver
$messages_sql = "
    SELECT m.*, u.full_name AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY m.sent_at DESC
";
$stmt = $conn->prepare($messages_sql);
$stmt->bind_param("ii", $doctor_id, $doctor_id);
$stmt->execute();
$messages_result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages from Patients | Doctor Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f3e8ff, #e0d4ff);
    color: #2e2e2e;
}

/* ===== Page Header ===== */
.page-header {
    background: linear-gradient(90deg, #6a0dad, #8e2de2);
    padding: 20px 30px;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.page-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
}
.page-header p {
    margin: 6px 0 0;
    font-size: 15px;
    opacity: 0.9;
}

/* ===== Container ===== */
.container {
    max-width: 1100px;
    margin: 40px auto;
    background: rgba(255,255,255,0.95);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.12);
    backdrop-filter: blur(6px);
    animation: fadeIn 0.7s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== Table ===== */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}

th {
    background: #6a0dad;
    color: white;
    padding: 14px;
    font-size: 15px;
    text-align: left;
}

td {
    padding: 14px;
    border-bottom: 1px solid #d9b6ff;
    vertical-align: top;
}

tr.from-patient {
    background: #f5eaff;
}

tr.from-you {
    background: #f0e0ff;
}

tr:hover {
    background: #e8d4ff;
    transition: 0.3s;
}

/* ===== Reply Form ===== */
textarea {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #b6a0d4;
    background: #f8f0ff;
    font-size: 14px;
    transition: 0.3s;
    resize: vertical;
}

textarea:focus {
    border-color: #6a0dad;
    box-shadow: 0 0 6px rgba(106,13,173,0.35);
    outline: none;
}

/* ===== Button ===== */
button {
    background: #6a0dad;
    color: white;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 5px;
}
button:hover {
    background: #8e2de2;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(106,13,173,0.3);
}

/* ===== Success Message ===== */
.success {
    background: #f3e8ff;
    padding: 12px;
    border-left: 5px solid #8e2de2;
    border-radius: 10px;
    font-weight: 600;
    color: #4b2970;
    margin-bottom: 20px;
    text-align: center;
}

/* ===== Back Link ===== */
.back-link {
    text-decoration: none;
    color: #6a0dad;
    font-weight: 600;
    display: inline-block;
    margin-top: 20px;
    transition: 0.3s;
}
.back-link:hover {
    text-decoration: underline;
    color: #8e2de2;
}
</style>
</head>
<body>

<div class="page-header">
    <h1>📨 Messages from Patients</h1>
    <p>Doctor: <?php echo htmlspecialchars($doctor_name); ?></p>
</div>

<div class="container">
    <?php if(!empty($success)) echo '<p class="success">'.htmlspecialchars($success).'</p>'; ?>

    <?php if($messages_result && $messages_result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Sender</th>
            <th>Message</th>
            <th>Sent At</th>
            <th>Reply</th>
        </tr>
        <?php while($msg = $messages_result->fetch_assoc()): ?>
        <?php $is_doctor = ($msg['sender_id'] == $doctor_id); ?>
        <tr class="<?php echo $is_doctor ? 'from-you' : 'from-patient'; ?>">
            <td><?php echo $is_doctor ? 'You' : htmlspecialchars($msg['sender_name']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
            <td><?php echo htmlspecialchars($msg['sent_at']); ?></td>
            <td>
                <?php if(!$is_doctor): ?>
                <form method="POST">
                    <input type="hidden" name="patient_id" value="<?php echo $msg['sender_id']; ?>">
                    <textarea name="reply_message" rows="2" placeholder="Type your reply..." required></textarea>
                    <button type="submit">Send Reply</button>
                </form>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No messages from patients yet.</p>
    <?php endif; ?>

    <p><a href="doctor_dashboard.php">← Back to Dashboard</a></p>
</div>

</body>
</html>
