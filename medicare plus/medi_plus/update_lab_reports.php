<?php
session_start();
include 'config/db_connect.php';

// Only allow doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Ensure variables are defined
$doctor_name = $_SESSION['full_name'] ?? 'Doctor';
$message = $message ?? "";

// Fetch patients for dropdown from users table
$patients_result = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role='patient' ORDER BY full_name ASC");

// Insert into lab_reports
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = mysqli_real_escape_string($conn, $_POST['patient_id']);
    $test_type = mysqli_real_escape_string($conn, $_POST['test_type']);
    $report_date = mysqli_real_escape_string($conn, $_POST['report_date']);
    $results = mysqli_real_escape_string($conn, $_POST['results']);
    $doctor_comments = mysqli_real_escape_string($conn, $_POST['doctor_comments']);

    // Get patient name
    $patient_query = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id='$patient_id'");
    $patient_row = mysqli_fetch_assoc($patient_query);
    $patient_name = $patient_row['full_name'] ?? 'Unknown';

    $file_path = ""; // handle file upload here...

    $insert_query = "
        INSERT INTO lab_reports 
        (patient_id, patient_name, doctor_id, test_type, report_date, results, doctor_comments, file_path)
        VALUES ('$patient_id', '$patient_name', '".$_SESSION['user_id']."', '$test_type', '$report_date', '$results', '$doctor_comments', '$file_path')
    ";

    if (!mysqli_query($conn, $insert_query)) {
        $message = "Error inserting report: " . mysqli_error($conn);
    } else {
        $message = "Lab report added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Lab Report | MediCare+</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ===== Global ===== */
body {
    margin: 0;
    padding: 0;
    font-family: "Poppins", sans-serif;
    background: linear-gradient(135deg, #f3e8ff, #e0d4ff);
    color: #2e2e2e;
}

/* ===== Header ===== */
header {
    background: linear-gradient(90deg, #6a0dad, #8e2de2);
    color: white;
    padding: 18px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
header h2 {
    font-size: 22px;
    font-weight: 600;
}
nav a {
    color: white;
    margin-left: 18px;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s ease;
}
nav a:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

/* ===== Container Card ===== */
.container {
    max-width: 750px;
    margin: 60px auto;
    background: rgba(255, 255, 255, 0.95);
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.12);
    backdrop-filter: blur(8px);
    animation: fadeIn 0.7s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

h1 {
    color: #6a0dad;
    font-size: 26px;
    margin-bottom: 25px;
    text-align: center;
}

/* ===== Form Controls ===== */
input, textarea, select {
    width: 100%;
    padding: 14px 12px;
    margin: 12px 0 20px 0;
    border-radius: 12px;
    border: 1px solid #b6a0d4;
    background: #f7f2ff;
    transition: all 0.3s ease;
    font-size: 15px;
}
input:focus, textarea:focus, select:focus {
    border-color: #6a0dad;
    box-shadow: 0 0 8px rgba(106,13,173,0.3);
    outline: none;
}

/* ===== File Upload ===== */
input[type="file"] {
    padding: 6px 12px;
    background: #f0e6ff;
}

/* ===== Button ===== */
button {
    width: 100%;
    padding: 16px;
    background: #6a0dad;
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}
button:hover {
    background: #8e2de2;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(106,13,173,0.3);
}

/* ===== Labels ===== */
label {
    font-weight: 500;
    color: #4b2970;
}

/* ===== Success / Error Message ===== */
.message {
    background: #f3e8ff;
    padding: 14px;
    border-left: 5px solid #8e2de2;
    border-radius: 10px;
    color: #4b2970;
    margin-bottom: 20px;
    font-weight: 600;
    text-align: center;
}
</style>
</head>
<body>

<header>
    <h2>Welcome, Dr. <?php echo htmlspecialchars($doctor_name); ?></h2>
    <nav>
        <a href="doctor_dashboard.php">Dashboard</a>
        <a href="view_lab_reports.php">View Reports</a>
        <a href="update_lab_reports.php">Add Reports</a>
        <a href="view_messages.php">Messages</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
<h1>🩺 Add New Lab Report</h1>

<?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Patient:</label>
    <select name="patient_id" required>
        <option value="">-- Select Patient --</option>
        <?php
        mysqli_data_seek($patients_result, 0); 
        while ($p = mysqli_fetch_assoc($patients_result)): ?>
            <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
        <?php endwhile; ?>
    </select>

    <label>Test Type:</label>
    <input type="text" name="test_type" required>

    <label>Report Date:</label>
    <input type="date" name="report_date" required>

    <label>Results:</label>
    <textarea name="results" rows="4" required></textarea>

    <label>Doctor Comments:</label>
    <textarea name="doctor_comments" rows="3"></textarea>

    <label>Attach Report File (optional):</label>
    <input type="file" name="report_file" accept=".pdf,.jpg,.jpeg,.png">

    <button type="submit">Submit Report</button>
</form>
</div>

</body>
</html>
