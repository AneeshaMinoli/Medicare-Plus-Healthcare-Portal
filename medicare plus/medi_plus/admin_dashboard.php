<?php
session_start();
require_once('config/db_connect.php');

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['full_name'];

// Flash message
$flash_message = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Fetch data safely
$doctor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='doctor'"))['total'];
$patient_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='patient'"))['total'];
// Fetch all doctors with user info
$doctor_query = "
    SELECT d.doctor_id, d.specialization, u.user_id, u.full_name, u.email, u.phone
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
";
$doctors_result = mysqli_query($conn, $doctor_query);


$patients_result = mysqli_query($conn, "SELECT user_id, full_name, email, phone FROM users WHERE role='patient' ORDER BY full_name ASC");
if (!$patients_result) $patients_result = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Medicare Plus</title>
<style>
:root {
    --primary: #7e0083ff;
    --primary-light: #be5ad7ff;
    --bg: #e8f5e9;
    --card-bg: #ffffff;
    --accent: #49056bff;
    --danger: #d32f2f;
    --text-dark: #6b097eff;
}
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: Arial,sans-serif; background: var(--bg); color: var(--text-dark); }

/* Header */
header {
    background: var(--primary);
    color: #fff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
header h2 { font-weight: 600; }
nav span { margin-right: 20px; }
nav a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    background: rgba(255,255,255,0.2);
    transition: background 0.2s;
}
nav a:hover { background: rgba(255,255,255,0.4); }

/* Container */
.container { padding: 30px; max-width: 1200px; margin: auto; }

/* Flash */
.flash {
    background: #8400afff;
    color: #ffffffff;
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 8px;
    border-left: 5px solid var(--primary);
    font-weight: 500;
}

/* Stats Cards */
.stats { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
.stats .card {
    flex: 1;
    min-width: 200px;
    background: var(--card-bg);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    text-align: center;
}
.stats .card h4 { margin-bottom: 10px; color: var(--primary); }
.stats .card p { font-size: 1.5rem; font-weight: 600; }

/* Section Titles */
h3 { color: var(--primary); margin-top: 30px; margin-bottom: 15px; }

/* Buttons */
.btn { padding: 8px 14px; border-radius: 6px; background: var(--accent); color: #fff; text-decoration: none; font-weight: 500; transition: all 0.2s; }
.btn:hover { background: var(--primary-light); }
.btn.delete-btn { background: var(--danger); }
.btn.delete-btn:hover { background: #ff0000ff; }

/* Tables */
table { width: 100%; border-collapse: collapse; margin-top: 15px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
th, td { padding: 12px 15px; text-align: left; }
th { background: var(--primary); color: #fff; font-weight: 600; }
tr:nth-child(even) { background: #f1fdf1; }
tr:hover { background: #e0f2e9; }

/* Responsive */
@media(max-width:768px){
    header { flex-direction: column; align-items: flex-start; }
    nav { margin-top: 10px; }
    .stats { flex-direction: column; }
    table, th, td { font-size: 14px; }
}
</style>
</head>
<body>
<header>
    <h2>Admin Dashboard</h2>
    <nav>
        <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
        <a href="login.php">Logout</a>
    </nav>
</header>

<div class="container">

<?php if($flash_message): ?>
<div class="flash"><?php echo htmlspecialchars($flash_message); ?></div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="stats">
    <div class="card">
        <h4>Total Doctors</h4>
        <p><?php echo $doctor_count; ?></p>
    </div>
    <div class="card">
        <h4>Total Patients</h4>
        <p><?php echo $patient_count; ?></p>
    </div>
</div>

<h3>➕ Add New Doctor</h3>
<a class="btn" href="add_doctor.php">Add Doctor</a>

<h3>👨‍⚕️ Doctors List</h3>
<?php if(!empty($doctors_result) && mysqli_num_rows($doctors_result) > 0): ?>
<table>
<tr><th>ID</th><th>Name</th><th>Email</th><th>Specialization</th><th>Phone</th><th>Actions</th></tr>
<?php while($doc = mysqli_fetch_assoc($doctors_result)): ?>
<tr>
<td><?php echo $doc['doctor_id']; ?></td>
<td><?php echo htmlspecialchars($doc['full_name']); ?></td>
<td><?php echo htmlspecialchars($doc['email']); ?></td>
<td><?php echo htmlspecialchars($doc['specialization']); ?></td>
<td><?php echo htmlspecialchars($doc['phone']); ?></td>
<td>
    <a class="btn" href="edit_user.php?id=<?php echo $doc['user_id']; ?>">Edit</a>
    <a class="btn delete-btn" href="delete_user.php?id=<?php echo $doc['user_id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
</td>

</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No doctors found.</p>
<?php endif; ?>

<h3>👥 Patients List</h3>
<?php if(!empty($patients_result) && mysqli_num_rows($patients_result) > 0): ?>
<table>
<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th></tr>
<?php while($pat = mysqli_fetch_assoc($patients_result)): ?>
<tr>
    <td><?php echo $pat['user_id']; ?></td>
    <td><?php echo htmlspecialchars($pat['full_name']); ?></td>
    <td><?php echo htmlspecialchars($pat['email']); ?></td>
    <td><?php echo htmlspecialchars($pat['phone']); ?></td>
    <td>
        <a class="btn" href="edit_user.php?id=<?php echo $pat['user_id']; ?>">Edit</a>
        <a class="btn delete-btn" href="delete_user.php?id=<?php echo $pat['user_id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No patients found.</p>
<?php endif; ?>

</div>
</body>
</html>
