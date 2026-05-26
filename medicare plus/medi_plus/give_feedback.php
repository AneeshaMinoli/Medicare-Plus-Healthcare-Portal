<?php
session_start();
require_once('config/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = trim($_POST['comment'] ?? '');

    if ($doctor_id && $rating) {
        // Get patient_id
        $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $patient = $res->fetch_assoc();
        $patient_id = $patient['patient_id'];
        $stmt->close();

        // Insert feedback
        $stmt = $conn->prepare("INSERT INTO feedbacks (doctor_id, patient_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $doctor_id, $patient_id, $rating, $comment);
        if ($stmt->execute()) $submitted = true;
        $stmt->close();
    }
}

// Fetch doctors
$doctors = $conn->query("SELECT d.doctor_id, u.full_name FROM doctors d JOIN users u ON d.user_id = u.user_id ORDER BY u.full_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Give Feedback</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin:0;
    background: linear-gradient(135deg, #f3e0ff, #d9b3ff);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

form {
    background:white;
    padding:35px;
    border-radius:20px;
    width:450px;
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
    position:relative;
}

h2 {
    text-align:center;
    color:#6a0dad;
    margin-bottom:25px;
}

label { font-weight:600; margin-top:15px; display:block; }
select, textarea, input[type=number] {
    width:100%; padding:10px 12px; margin-top:8px;
    border-radius:10px; border:1px solid #b19cd9;
    background:#faf5ff; font-size:14px;
}
select:focus, textarea:focus, input:focus {
    outline:none;
    border-color:#6a0dad;
    box-shadow:0 0 6px rgba(106,13,173,0.3);
}

button {
    width:100%;
    background:#6a0dad;
    color:white;
    border:none;
    padding:12px;
    border-radius:12px;
    font-size:16px;
    font-weight:600;
    margin-top:20px;
    cursor:pointer;
    transition:0.3s ease;
}
button:hover { background:#530d99; transform:translateY(-2px); }

.stars {
    display:flex; flex-direction:row-reverse; justify-content:flex-end; margin-top:8px;
}
.stars input { display:none; }
.stars label {
    font-size:28px; color:#ccc; cursor:pointer; transition:0.3s;
}
.stars input:checked ~ label,
.stars label:hover,
.stars label:hover ~ label { color:#6a0dad; }

/* Popup */
#popup {
    position:fixed;
    top:50%; left:50%;
    transform:translate(-50%, -50%) scale(0);
    background:white; padding:30px 35px;
    border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2);
    font-weight:600; font-size:18px;
    text-align:center;
    color:#4b2970;
    z-index:100;
    transition:0.5s ease;
}
#popup.show { transform:translate(-50%, -50%) scale(1); }
</style>
</head>
<body>

<form action="" method="POST">
    <h2>Give Feedback</h2>

    <label>Select Doctor</label>
    <select name="doctor_id" required>
        <option value="">-- Select Doctor --</option>
        <?php while ($dr = $doctors->fetch_assoc()): ?>
            <option value="<?= $dr['doctor_id'] ?>"><?= htmlspecialchars($dr['full_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Rating (1-5)</label>
    <input type="number" name="rating" min="1" max="5" required>

    <label>Feedback</label>
    <textarea name="comment" required></textarea>

    <button type="submit">Submit Feedback</button>
</form>

<div id="popup">✔ Feedback submitted successfully!</div>

<?php if ($submitted): ?>
<script>
document.getElementById('popup').classList.add('show');
setTimeout(()=>{document.getElementById('popup').classList.remove('show');}, 3000);
</script>
<?php endif; ?>

</body>
</html>
