<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = sanitize($_POST['user_type'] ?? '');

    if (empty($email) || empty($password) || empty($user_type)) {
        $error = 'All fields are required';
    } else {
        $conn = getDBConnection();

        // Determine which table to query based on user type
        $table = '';
        $id_field = '';
        $name_field = '';

        switch ($user_type) {
            case 'admin':
                $table = 'admin';
                $id_field = 'admin_id';
                $name_field = 'name';
                break;
            case 'worker':
                $table = 'worker';
                $id_field = 'worker_id';
                $name_field = 'worker_name';
                break;
            case 'customer':
                $table = 'customer';
                $id_field = 'customer_id';
                $name_field = 'name';
                break;
            default:
                $error = 'Invalid user type';
        }

        if (empty($error)) {
            $stmt = $conn->prepare("SELECT $id_field, $name_field, email, password FROM $table WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if ($password === $user['password']) {
                    // Set session variables
                    $_SESSION['user_id'] = $user[$id_field];
                    $_SESSION['user_name'] = $user[$name_field];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user_type;

                    setSuccess('Login successful! Welcome back, ' . $user[$name_field]);
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }

            $stmt->close();
        }

        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Welcome Back</h1>
            <p class="subtitle">Sign in to continue to <?php echo SITE_NAME; ?></p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="user_type">Login As</label>
                    <select name="user_type" id="user_type" required>
                        <option value="">Select User Type</option>
                        <option value="admin">Admin</option>
                        <option value="worker">Worker</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required
                           placeholder="Enter your email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register_new.php">Sign up here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
