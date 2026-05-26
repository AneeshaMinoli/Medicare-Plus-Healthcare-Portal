<?php
session_start();

// Ensure only doctors can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: view_lab_results.php");
    exit();
}

include 'config/db_connect.php';

// Get doctor info from session
$doctor_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['full_name'] ?? 'Doctor';

// Fetch lab reports
$query = "SELECT * FROM lab_reports WHERE doctor_id = '$doctor_id' ORDER BY report_date DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("❌ Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Lab Reports | MediCare+</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f2f0f7;
        margin: 0;
        color: #2e2e2e;
    }
    header {
        background: linear-gradient(90deg, #6a0dad, #8e2de2);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    header h2 {
        margin: 0;
        font-weight: 600;
        font-size: 20px;
    }
    nav a {
        color: white;
        margin-left: 25px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    nav a:hover {
        color: #ffd1ff;
        transform: scale(1.05);
    }
    .container {
        padding: 40px;
        max-width: 1200px;
        margin: auto;
    }
    h1 {
        color: #6a0dad;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .download-btn {
        display: inline-block;
        padding: 10px 20px;
        margin-bottom: 20px;
        background-color: #8e2de2;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .download-btn:hover {
        background-color: #a64bf2;
        transform: translateY(-2px);
    }
    .cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
    .card {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        animation: fadeIn 1s ease forwards;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .card h3 {
        margin-top: 0;
        color: #6a0dad;
        font-size: 18px;
        margin-bottom: 10px;
    }
    .card p {
        margin: 6px 0;
        color: #4b3b66;
    }
    .card a.btn {
        display: inline-block;
        margin-top: 10px;
    }
    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(20px);}
        100% { opacity: 1; transform: translateY(0);}
    }
    @media screen and (max-width: 768px) {
        .cards-container {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>
<header>
    <h2>Welcome, Dr. <?php echo htmlspecialchars($doctor_name); ?></h2>
    <nav>
      <a href="doctor_dashboard.php">Dashboard</a>
      <a href="view_lab_results.php">View Reports</a>
      <a href="update_lab_reports.php">Update Reports</a>
      <a href="view_messages.php">Messages</a>
      <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>🧾 Your Patient Lab Reports</h1>
    <a class="download-btn" href="download_all_reports.php">⬇️ Download All Reports</a>

    <?php if (isset($result) && mysqli_num_rows($result) > 0): ?>
        <div class="cards-container">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['patient_name']); ?></h3>
                    <p><strong>Test:</strong> <?php echo htmlspecialchars($row['test_type']); ?></p>
                    <p><strong>Date:</strong> <?php echo $row['report_date']; ?></p>
                    <p><strong>Results:</strong> <?php echo htmlspecialchars($row['results']); ?></p>
                    <p><strong>Comments:</strong> <?php echo htmlspecialchars($row['doctor_comments']); ?></p>
                    <?php if (!empty($row['file_path'])): ?>
                        <a class="btn" href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View File</a>
                    <?php else: ?>
                        <p>No file</p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No lab reports found for your patients.</p>
    <?php endif; ?>
</div>
</body>
</html>
