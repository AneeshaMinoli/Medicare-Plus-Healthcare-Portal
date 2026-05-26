<?php
session_start();
require_once('config/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $specialization = trim($_POST['specialization']);

    // Check if username or email already exists
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Username or email already exists!";
    } else {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password, role, phone) VALUES (?, ?, ?, ?, 'doctor', ?)");
        $stmt->bind_param("sssss", $full_name, $username, $email, $password, $phone);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insert specialization into doctors table
            $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialization) VALUES (?, ?)");
            $stmt2->bind_param("is", $user_id, $specialization);
            $stmt2->execute();

            $success = "Doctor added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Doctor | Admin Dashboard</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f7f9; padding:20px; }
form { background:white; padding:20px; border-radius:10px; max-width:500px; margin:auto; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
input { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
button { padding:10px 15px; background:#008080; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#006666; }
.message { text-align:center; margin-bottom:15px; font-weight:bold; }
.error { color:red; }
.success { color:green; }
</style>
</head>
<body>

<h2 style="text-align:center;">Add New Doctor</h2>

<?php
if ($error) echo "<div class='message error'>$error</div>";
if ($success) echo "<div class='message success'>$success</div>";
?>

<form method="POST">
    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="specialization" placeholder="Specialization" required>
    <input type="text" name="phone" placeholder="Phone">
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Add Doctor</button>
</form>

<p style="text-align:center; margin-top:15px;"><a href="admin_dashboard.php">← Back to Dashboard</a></p>

</body>
</html>
