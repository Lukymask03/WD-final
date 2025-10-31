<?php
session_start();
require_once __DIR__ . "/../backend/db.php";
require_once __DIR__ . "/../backend/helpers/log_activity.php";

if (isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $role = $_POST["role"];

    $fullname = $_POST["fullname"] ?? null;
    $team = $_POST["team"] ?? null;
    $age = $_POST["age"] ?? null;

    // Validate role
    if (!in_array($role, ["player", "organizer"])) {
        $_SESSION["message"] = "Invalid role selected.";
        header("Location: create_account.php");
        exit;
    }

    try {
        // Check duplicates
        $check = $conn->prepare("SELECT 1 FROM accounts WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);

        if ($check->rowCount() > 0) {
            $_SESSION["message"] = "Username or Email already exists.";
            header("Location: create_account.php");
            exit;
        }

        // Insert into accounts table
        $stmt = $conn->prepare("
            INSERT INTO accounts (username, email, password, role, fullname, team, age, account_status)
            VALUES (:username, :email, :password, :role, :fullname, :team, :age, 'active')
        ");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role,
            ':fullname' => $fullname,
            ':team' => $team,
            ':age' => $age
        ]);

        $account_id = $conn->lastInsertId();

        // Log the activity
        logActivity($account_id, "Registered new account", "Role: $role");

        $_SESSION["message"] = "Account created successfully! You can now log in.";
        header("Location: ../auth/login.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION["message"] = "Database error: " . $e->getMessage();
        header("Location: create_account.php");
        exit;
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account - GameX</title>
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/create_account.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="left-panel">
        <h2>Welcome to Game X</h2>
    </div>

   <div class="right-panel">
  <form action="create_account.php" method="POST">
    <h2>Create Account</h2>

    <!-- ✅ NEW USERNAME FIELD -->
    <input type="text" name="username" placeholder="Username" required>

    <input type="email" name="email" placeholder="Email Address" required>

    <div class="password-wrapper">
      <input type="password" name="password" id="password" placeholder="Password" required>
      <i class="fa fa-eye" id="togglePassword"></i>
    </div>

    <select name="role" id="role" required>
      <option value="">Select Role</option>
      <option value="player">Player</option>
      <option value="organizer">Organizer</option>
    </select>

    <!-- PLAYER FIELDS -->
    <div id="player-fields" style="display:none;">
      <input type="text" name="fullname" placeholder="Full Name">
      <input type="text" name="gamer_tag" placeholder="Gamer Tag">
      <input type="number" name="age" placeholder="Age">
    </div>

    <!-- ORGANIZER FIELDS -->
    <div id="organizer-fields" style="display:none;">
      <input type="text" name="organization" placeholder="Organization Name">
      <input type="text" name="contact_no" placeholder="Contact Number">
      <input type="text" name="website" placeholder="Website (Optional)">
    </div>

    <button type="submit" name="register">Create Account</button>
    <a href="login.php" class="back-btn">Back to Login</a>
  </form>
</div>

</div>
<script>
function toggleVisibility(icon, input) {
    const type = input.type === "password" ? "text" : "password";
    input.type = type;
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");
}

// Toggle for password field (always exists)
const passwordIcon = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");
if (passwordIcon && passwordInput) {
    passwordIcon.addEventListener("click", () => toggleVisibility(passwordIcon, passwordInput));
}

// Toggle for confirm password field (only if exists)
const confirmIcon = document.getElementById("toggleConfirm");
const confirmInput = document.getElementById("confirm_password");
if (confirmIcon && confirmInput) {
    confirmIcon.addEventListener("click", () => toggleVisibility(confirmIcon, confirmInput));
}

// Role-based field toggle
const roleSelect = document.getElementById("role");
const playerFields = document.getElementById("player-fields");
const organizerFields = document.getElementById("organizer-fields");

if (roleSelect) {
    roleSelect.addEventListener("change", () => {
        playerFields.style.display = roleSelect.value === "player" ? "block" : "none";
        organizerFields.style.display = roleSelect.value === "organizer" ? "block" : "none";
    });
}
</script>

<script src="../assets/js/validate_form.js"></script>
<script src="../assets/js/password_strength.js"></script>
<script src="../assets/js/index.js"></script>
</body>
</html>



