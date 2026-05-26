<?php
session_start();
require_once('config/db_connect.php'); 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = false;
$error = "";

// When form submits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sender_id = $_SESSION['user_id'];
    $receiver_id = intval($_POST['receiver_id'] ?? 0);
    $message_text = trim($_POST['message_text'] ?? '');

    // Validation
    if ($receiver_id <= 0 || empty($message_text)) {
        $error = "Please select a doctor and write a message.";
    } else {

        // Correct query for YOUR database structure
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, message, sent_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message_text);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Error sending message: " . $stmt->error;
        }
    }
}

// Fetch all doctors
$doctors_result = mysqli_query($conn, "
    SELECT u.user_id, u.full_name, d.specialization
    FROM users u
    JOIN doctors d ON u.user_id = d.user_id
    ORDER BY u.full_name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Send Message | Medicare Plus</title>
<style>
body { font-family: Arial, sans-serif; background:#f0f4f8; padding:50px; }
.container { max-width:500px; margin:auto; background:#fff; padding:30px; border-radius:12px; 
    box-shadow:0 10px 25px rgba(0,0,0,0.1); }
h2 { text-align:center; color:#2c3e50; margin-bottom:20px; }
select, textarea, button { width:100%; padding:10px; margin:10px 0; border-radius:6px; border:1px solid #ccc; font-size:16px; }
button { background: #ab0ac4ff; color:white; border:none; cursor:pointer; transition:0.3s; }
button:hover { background:#2ecc71; }
.success { color:#27ae60; font-weight:bold; margin-bottom:15px; }
.error { color:#d32f2f; font-weight:bold; margin-bottom:15px; }
</style>
</head>
<body>
<div class="container">
<h2>Send Message to Doctor</h2>

<?php if ($success): ?>
    <div class="success">✅ Message sent successfully!</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <label>Select Doctor:</label>
    <select name="receiver_id" required>
        <option value="">-- Choose Doctor --</option>
        <?php while($doc = mysqli_fetch_assoc($doctors_result)): ?>
            <option value="<?= $doc['user_id'] ?>">
                <?= htmlspecialchars($doc['full_name']) ?> (<?= htmlspecialchars($doc['specialization']) ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Message:</label>
    <textarea name="message_text" placeholder="Type your message..." required rows="5"></textarea>

    <button type="submit">Send Message</button>
</form>
</div>
</body>
</html>
