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
    $password = $_POST['password'];

    // Validation
    if (empty($username) || empty($password) || empty($user_type)) {
        $error = 'All fields are required!';
    } else {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT id, username, password, full_name, user_type, email FROM users WHERE username = ? AND user_type = ?");
        $stmt->bind_param("ss", $username, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if ($password === $user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['email'] = $user['email'];

                $stmt->close();
                closeDBConnection($conn);

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid username or password!';
            }
        } else {
            $error = 'Invalid username or password!';
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
    <title>Login - Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Welcome Back!</h1>
                <p>Sign in to continue to Task Manager</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form">
                <div class="user-type-selector">
                    <h3>Select User Type</h3>
                    <div class="user-type-options">
                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="customer" required>
                            <div class="card-content">
                                <div class="icon">👤</div>
                                <h4>Customer</h4>
                                <p>Create and manage your tasks</p>
                            </div>
                        </label>

                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="worker" required>
                            <div class="card-content">
                                <div class="icon">👷</div>
                                <h4>Worker</h4>
                                <p>View and complete assigned tasks</p>
                            </div>
                        </label>

                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="admin" required>
                            <div class="card-content">
                                <div class="icon">👨‍💼</div>
                                <h4>Admin</h4>
                                <p>Full system management access</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           placeholder="Enter your username"
                           required
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
