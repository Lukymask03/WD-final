<?php
require_once "TOURNAMENT/backend/db.php";

// Admin credentials - CHANGE THESE!
$admin_username = "admin";
$admin_email = "admin@gamex.com";
$admin_password = "Admin@123";  // Change this to your desired password

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

try {
    // Check if admin already exists
    $check = $conn->prepare("SELECT * FROM accounts WHERE email = ? OR username = ?");
    $check->execute([$admin_email, $admin_username]);
    
    if ($check->rowCount() > 0) {
        echo "❌ Admin account already exists!";
    } else {
        // Insert admin account
        $stmt = $conn->prepare("
            INSERT INTO accounts (username, email, password, role, is_admin, account_status, created_at)
            VALUES (?, ?, ?, 'player', 1, 'active', NOW())
        ");
        $stmt->execute([$admin_username, $admin_email, $hashed_password]);
        
        echo "✅ Admin account created successfully!<br>";
        echo "Username: $admin_username<br>";
        echo "Email: $admin_email<br>";
        echo "Password: $admin_password<br>";
        echo "<br><strong>⚠️ DELETE THIS FILE IMMEDIATELY AFTER USE!</strong>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>