<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($user_type)) {
        $error = 'Please fill in all required fields!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address!';
    } else {
        $conn = getDBConnection();

        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username already exists!';
        } else {
            $stmt->close();

            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = 'Email already exists!';
            } else {
                $stmt->close();

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $username, $email, $password, $full_name, $user_type, $phone, $address);

                if ($stmt->execute()) {
                    $success = 'Registration successful! You can now login.';
                    header("refresh:2;url=login.php");
                } else {
                    $error = 'Error creating account: ' . $conn->error;
                }
            }
        }

        $stmt->close();
        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box register-box">
            <div class="login-header">
                <h1>Create Account</h1>
                <p>Join Task Manager today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="login-form">
                <div class="user-type-selector">
                    <h3>Select User Type *</h3>
                    <div class="user-type-options">
                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="customer" required>
                            <div class="card-content">
                                <div class="icon">👤</div>
                                <h4>Customer</h4>
                                <p>Create and manage tasks</p>
                            </div>
                        </label>

                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="worker" required>
                            <div class="card-content">
                                <div class="icon">👷</div>
                                <h4>Worker</h4>
                                <p>Complete assigned tasks</p>
                            </div>
                        </label>

                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="admin" required>
                            <div class="card-content">
                                <div class="icon">👨‍💼</div>
                                <h4>Admin</h4>
                                <p>Manage the system</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text"
                               id="full_name"
                               name="full_name"
                               placeholder="Enter your full name"
                               required
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text"
                               id="username"
                               name="username"
                               placeholder="Choose a username"
                               required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email"
                           id="email"
                           name="email"
                           placeholder="Enter your email"
                           required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Choose a password (min 6 chars)"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               placeholder="Re-enter password"
                               required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               placeholder="Enter phone number"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text"
                               id="address"
                               name="address"
                               placeholder="Enter address"
                               value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <div class="login-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
