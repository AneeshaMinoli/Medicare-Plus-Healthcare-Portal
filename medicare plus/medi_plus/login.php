<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/db_connect.php');

// ---------------- LOGIN PROCESS ----------------
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username' OR email='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'doctor')
                header("Location: doctor_dashboard.php");
            elseif ($user['role'] === 'patient')
                header("Location: patient_dashboard.php");
            else
                header("Location: admin_dashboard.php");
            exit();
        } else {
            $error_msg = "Invalid password.";
        }
    } else {
        $error_msg = "No user found with that username/email.";
    }
}

// ---------------- REGISTER PROCESS ----------------
$registered = false;
if (isset($_POST['register'])) {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password_plain = mysqli_real_escape_string($conn, $_POST['password']);
    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $specialization = ($role === 'doctor') ? mysqli_real_escape_string($conn, $_POST['specialization']) : NULL;

    $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $error_msg = "Username or email already exists.";
    } else {
        $insert_user = "INSERT INTO users (full_name, username, email, phone, password, role)
                VALUES ('$fullname', '$username', '$email', '$phone', '$password', '$role')";

        if (mysqli_query($conn, $insert_user)) {
            $user_id = mysqli_insert_id($conn);

            // If doctor, insert into doctors table
            if ($role === 'doctor') {
                if (empty($specialization)) {
                    die("Error: Doctor specialization is required.");
                }
                $insert_doctor = "INSERT INTO doctors (user_id, specialization)
                                  VALUES ($user_id, '$specialization')";
                mysqli_query($conn, $insert_doctor);
            }

            // Registration successful → show Sign In form
            $registered = true;
        } else {
            $error_msg = "Registration failed: " . mysqli_error($conn);
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register | Medicare Plus</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            height: 100vh;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 900px;
            height: 550px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            overflow: hidden;
            position: relative;
            transition: 1s ease-in-out;
        }

        .panel {
            width: 50%;
            padding: 40px;
            transition: 0.8s ease;
        }

        .left-panel {
            background: #f594fcff;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .left-panel img {
            width: 120%;
        }

        h2 {
            color: #7f0b9fff;
            margin-bottom: 10px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .btn {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 10px;
            background: #88039dff;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0d47a1;
        }

        .small-text {
            margin-top: 15px;
            text-align: center;
        }

        .small-text a {
            color: #790a93ff;
            cursor: pointer;
            font-weight: 600;
        }

        #error,
        #success {
            margin-top: 10px;
            text-align: center;
            font-weight: 600;
        }

        #error {
            color: #ff0000ff;
        }

        #success {
            color: #2e7d32;
        }

        /* PANEL SLIDE */
        .container.sign-in-mode .left-panel {
            transform: translateX(100%);
        }

        .container.sign-in-mode .right-panel {
            transform: translateX(-100%);
        }
    </style>

</head>

<body>

    <div class="container <?php echo $registered ? 'sign-in-mode' : ''; ?>" id="container">

        <!-- LEFT PANEL -->
        <div class="panel left-panel">
            <img src="login.png" alt="Doctor">
        </div>

        <!-- RIGHT PANEL -->
        <div class="panel right-panel">

            <!-- SIGN UP FORM -->
            <div id="signUpForm" style="<?php echo $registered ? 'display:none;' : ''; ?>">
                <h2>Create Account</h2>

                <form method="POST">
                    <div class="form-group"><input type="text" name="fullname" placeholder="Full Name" required></div>
                    <div class="form-group"><input type="text" name="username" placeholder="Username" required></div>
                    <div class="form-group"><input type="email" name="email" placeholder="Email" required></div>
                    <div class="form-group"><input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group"><input type="text" name="phone" placeholder="Phone" required></div>

                    <div class="form-group">
                        <select name="role" id="roleSelect" onchange="toggleSpecialization()" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="patient">Patient</option>
                        </select>
                    </div>

                    <div class="form-group" id="specializationField" style="display:none;">
                        <input type="text" name="specialization" placeholder="Specialization">
                    </div>

                    <button class="btn" name="register">Sign Up</button>
                </form>

                <p class="small-text">Already have an account?
                    <a onclick="switchToSignIn()">Sign In</a>
                </p>

                <?php if (isset($error_msg))
                    echo "<p id='error'>$error_msg</p>"; ?>
            </div>


            <!-- SIGN IN FORM -->
            <div id="signInForm" style="<?php echo $registered ? '' : 'display:none;'; ?>">
                <h2>Welcome Back</h2>

                <form method="POST">
                    <div class="form-group"><input type="text" name="username" placeholder="Username or Email" required>
                    </div>
                    <div class="form-group"><input type="password" name="password" placeholder="Password" required>
                    </div>

                    <button class="btn" name="login">Sign In</button>
                </form>

                <p class="small-text">Don’t have an account?
                    <a onclick="switchToSignUp()">Sign Up</a>
                </p>

                <?php if ($registered)
                    echo "<p id='success'>Registration successful! Please sign in.</p>"; ?>
                <?php if (isset($error_msg) && !$registered)
                    echo "<p id='error'>$error_msg</p>"; ?>
            </div>

        </div>
    </div>

    <script>
        function toggleSpecialization() {
            const role = document.getElementById("roleSelect").value;
            document.getElementById("specializationField").style.display =
                (role === "doctor") ? "block" : "none";
        }

        function switchToSignIn() {
            document.getElementById("container").classList.add("sign-in-mode");
            document.getElementById("signUpForm").style.display = "none";
            setTimeout(() => {
                document.getElementById("signInForm").style.display = "block";
            }, 500);
        }

        function switchToSignUp() {
            document.getElementById("container").classList.remove("sign-in-mode");
            document.getElementById("signInForm").style.display = "none";
            setTimeout(() => {
                document.getElementById("signUpForm").style.display = "block";
            }, 500);
        }
    </script>

</body>
</html>