<?php
session_start();
require_once('config/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Get doctor_id
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$doctor_id = $res->fetch_assoc()['doctor_id'];
$stmt->close();

// Fetch feedback
$feedbacks = $conn->prepare("
SELECT f.rating, f.comment, f.created_at, u.full_name AS patient_name
FROM feedbacks f
JOIN patients p ON f.patient_id = p.patient_id
JOIN users u ON p.user_id = u.user_id
WHERE f.doctor_id=?
ORDER BY f.created_at DESC
");
$feedbacks->bind_param("i",$doctor_id);
$feedbacks->execute();
$result=$feedbacks->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Feedback</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins', sans-serif; margin:0; padding:30px; background:#f3e0ff; }
h2 { text-align:center; color:#6a0dad; margin-bottom:20px; }
.feedback-card {
    background:white; border-radius:15px; padding:20px; margin-bottom:15px;
    box-shadow:0 8px 20px rgba(106,13,173,0.15);
}
.feedback-card strong { color:#4b2970; font-size:16px; }
.feedback-card .rating { color:#ffb700; font-size:16px; margin-left:10px; }
.feedback-card p { margin:8px 0; color:#3b2b50; }
.feedback-card small { color:#8e7bb8; }
</style>
</head>
<body>

<h2>All Patient Feedback</h2>

<?php if($result->num_rows>0): ?>
    <?php while($fb=$result->fetch_assoc()): ?>
        <div class="feedback-card">
            <strong><?= htmlspecialchars($fb['patient_name']) ?></strong>
            <span class="rating"><?= str_repeat("★",(int)$fb['rating']).str_repeat("☆",5-(int)$fb['rating']) ?></span>
            <p><?= nl2br(htmlspecialchars($fb['comment'])) ?></p>
            <small><?= date("M d, Y h:i A", strtotime($fb['created_at'])) ?></small>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center; color:#6a0dad;">No feedback yet.</p>
<?php endif; ?>

</body>
</html>
