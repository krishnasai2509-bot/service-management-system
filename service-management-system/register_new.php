<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = sanitize($_POST['user_type'] ?? '');
    $street = sanitize($_POST['street'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');

    // Worker-specific fields
    $skill_type = sanitize($_POST['skill_type'] ?? '');
    $experience_years = sanitize($_POST['experience_years'] ?? '0');
    $category_id = sanitize($_POST['category_id'] ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($user_type)) {
        $error = 'All required fields must be filled';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($user_type === 'worker' && empty($skill_type)) {
        $error = 'Skill type is required for workers';
    } else {
        $conn = getDBConnection();
        // Check if email already exists in any user table
        $email_exists = false;
        $tables = ['admin' => 'admin_id', 'worker' => 'worker_id', 'customer' => 'customer_id'];

        foreach ($tables as $table => $id_field) {
            $check_stmt = $conn->prepare("SELECT $id_field FROM $table WHERE email = ?");
            $check_stmt->bind_param('s', $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $email_exists = true;
                break;
            }
            $check_stmt->close();
        }

        if ($email_exists) {
            $error = 'Email already registered';
        } else {
            // Insert based on user type
            if ($user_type === 'worker') {
                $stmt = $conn->prepare("INSERT INTO worker (worker_name, email, phone_no, password, skill_type, experience_years, street, city, pincode, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssdsssi', $name, $email, $phone, $password, $skill_type, $experience_years, $street, $city, $pincode, $category_id);
            } elseif ($user_type === 'customer') {
                $stmt = $conn->prepare("INSERT INTO customer (name, email, phone, password, street, city, pincode) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssss', $name, $email, $phone, $password, $street, $city, $pincode);
            } else {
                $error = 'Admin registration is not allowed';
            }

            if (empty($error) && $stmt->execute()) {
                if ($user_type === 'worker') {
                    setSuccess('Registration successful! Please login and set up your availability.');
                } else {
                    setSuccess('Registration successful! Please login to continue.');
                }
                header('Location: login_new.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }

            if (isset($stmt)) {
                $stmt->close();
            }
        }

        closeDBConnection($conn);
    }
}

// Get service categories for worker registration
$categories = [];
$conn = getDBConnection();
$result = $conn->query("SELECT category_id, category_name FROM service_category ORDER BY category_name");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .worker-fields {
            display: none;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Create Account</h1>
            <p class="subtitle">Join <?php echo SITE_NAME; ?> today</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="user_type">Register As *</label>
                    <select name="user_type" id="user_type" required>
                        <option value="">Select User Type</option>
                        <option value="customer">Customer</option>
                        <option value="worker">Service Worker</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required
                           placeholder="Enter your full name"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required
                           placeholder="Enter your email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required
                           placeholder="Enter your phone number"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="worker-fields">
                    <div class="form-group">
                        <label for="skill_type">Skill/Specialization *</label>
                        <input type="text" id="skill_type" name="skill_type"
                               placeholder="e.g., Licensed Plumber, Electrician"
                               value="<?php echo isset($_POST['skill_type']) ? htmlspecialchars($_POST['skill_type']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category_id">Service Category *</label>
                        <select name="category_id" id="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="experience_years">Years of Experience</label>
                        <input type="number" id="experience_years" name="experience_years"
                               min="0" step="0.5" value="0"
                               placeholder="Years of experience">
                    </div>
                </div>

                <div class="form-group">
                    <label for="street">Street Address</label>
                    <input type="text" id="street" name="street"
                           placeholder="Enter street address"
                           value="<?php echo isset($_POST['street']) ? htmlspecialchars($_POST['street']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city"
                               placeholder="City"
                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="pincode">Pincode</label>
                        <input type="text" id="pincode" name="pincode"
                               placeholder="Pincode"
                               value="<?php echo isset($_POST['pincode']) ? htmlspecialchars($_POST['pincode']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Create a password (min 6 characters)">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Confirm your password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login_new.php">Sign in here</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            const workerFields = document.querySelector('.worker-fields');
            const skillType = document.getElementById('skill_type');
            const categoryId = document.getElementById('category_id');

            if (this.value === 'worker') {
                workerFields.style.display = 'block';
                skillType.required = true;
                categoryId.required = true;
            } else {
                workerFields.style.display = 'none';
                skillType.required = false;
                categoryId.required = false;
            }
        });
    </script>
</body>
</html>
