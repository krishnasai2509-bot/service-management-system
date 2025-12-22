<?php
// Test Login Script
require_once 'config.php';

echo "<h1>Login Test Script</h1>";
echo "<pre>";

// Test credentials
$test_email = 'admin@taskmanager.com';
$test_password = 'password123';

echo "Testing login for: $test_email\n";
echo "Password: $test_password\n\n";

$conn = getDBConnection();

// Check if admin exists
$stmt = $conn->prepare("SELECT admin_id, name, email, password FROM admin WHERE email = ?");
$stmt->bind_param('s', $test_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo "✅ Admin found in database\n\n";
    $user = $result->fetch_assoc();

    echo "Admin Details:\n";
    echo "- ID: " . $user['admin_id'] . "\n";
    echo "- Name: " . $user['name'] . "\n";
    echo "- Email: " . $user['email'] . "\n";
    echo "- Password: " . substr($user['password'], 0, 30) . "...\n\n";

    // Test password verification
    if ($test_password === $user['password']) {
        echo "✅ Password verification: SUCCESS!\n";
        echo "✅ Login should work!\n\n";
    } else {
        echo "❌ Password verification: FAILED!\n";
        echo "❌ The password in database doesn't match 'password123'\n\n";

        echo "To fix, run this SQL:\n";
        echo "UPDATE admin SET password = '$test_password' WHERE email = '$test_email';\n";
    }
} else {
    echo "❌ Admin not found in database!\n";
}

$stmt->close();

echo "\n-----------------------------------\n\n";
echo "Testing all user types:\n\n";

// Test all user types
$test_users = [
    ['type' => 'admin', 'email' => 'admin@taskmanager.com'],
    ['type' => 'worker', 'email' => 'robert.p@worker.com'],
    ['type' => 'customer', 'email' => 'john.doe@email.com']
];

foreach ($test_users as $test) {
    $table = $test['type'];
    $id_field = $test['type'] . '_id';
    if ($test['type'] === 'admin') {
        $id_field = 'admin_id';
        $name_field = 'name';
    } elseif ($test['type'] === 'worker') {
        $name_field = 'worker_name';
    } else {
        $name_field = 'name';
    }

    $check = $conn->query("SELECT $id_field, $name_field, email FROM $table WHERE email = '{$test['email']}'")->fetch_assoc();
    if ($check) {
        echo "✅ {$test['type']}: {$test['email']} - EXISTS\n";
    } else {
        echo "❌ {$test['type']}: {$test['email']} - NOT FOUND\n";
    }
}

closeDBConnection($conn);

echo "</pre>";

echo "<hr>";
echo "<h2>Quick Login Links</h2>";
echo "<a href='login_new.php'>Go to Login Page</a>";
?>
