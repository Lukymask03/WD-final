<?php 
session_start();
require_once __DIR__ . "/../backend/db.php";
require_once __DIR__ . "/../backend/helpers/log_activity.php";
require __DIR__ . '/../backend/notifications/notification_engine.php';

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
        $_SESSION["error_message"] = "Invalid role selected.";
        header("Location: create_account.php");
        exit;
    }

    try {
        // Check duplicates
        $check = $conn->prepare("SELECT 1 FROM accounts WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);

        if ($check->rowCount() > 0) {
            $_SESSION["error_message"] = "Username or Email already exists.";
            header("Location: create_account.php");
            exit;
        }

        // Insert account
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

        // 🔥 Log activity
        logActivity($account_id, "Registered new account", "Role: $role");

        // 🔥 SEND WELCOME EMAIL (correct)
        triggerNotification('new_account', [
            'email'    => $email,
            'username' => $username
        ]);

        $_SESSION["success_message"] = "Account created successfully!";
        $_SESSION["success_username"] = $username;

        header("Location: create_account.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION["error_message"] = "Database error: " . $e->getMessage();
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
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="../assets/css/sweetalert2.min.css">
</head>

<body>
    <div class="container">
        <div class="left-panel">
            <h2>Welcome to Game X</h2>
        </div>

        <div class="right-panel">
            <form action="create_account.php" method="POST">
                <h2>Create Account</h2>

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

    <!-- SweetAlert2 JS -->
    <script src="../assets/js/sweetalert2.all.min.js"></script>

    <script>
        // Password toggle
        const togglePassword = document.getElementById("togglePassword");
        const passwordInput = document.getElementById("password");

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener("click", function() {
                const type = passwordInput.type === "password" ? "text" : "password";
                passwordInput.type = type;
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        }

        // Role-based fields toggle
        const roleSelect = document.getElementById("role");
        const playerFields = document.getElementById("player-fields");
        const organizerFields = document.getElementById("organizer-fields");

        roleSelect.addEventListener("change", function() {
            if (this.value === "player") {
                playerFields.style.display = "block";
                organizerFields.style.display = "none";
            } else if (this.value === "organizer") {
                playerFields.style.display = "none";
                organizerFields.style.display = "block";
            } else {
                playerFields.style.display = "none";
                organizerFields.style.display = "none";
            }
        });

        // Success Alert
        <?php if (isset($_SESSION["success_message"])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Account Created! 🎮',
                html: '<p style="font-size: 1rem; margin-top: 10px;">Welcome to GameX, <strong><?= htmlspecialchars($_SESSION["success_username"] ?? "Player") ?></strong>!</p>',
                confirmButtonText: 'Login Now',
                confirmButtonColor: '#ff6600',
                allowOutsideClick: false,
                background: '#1a1a1a',
                color: '#fff',
                customClass: {
                    popup: 'swal-dark-theme',
                    confirmButton: 'swal-confirm-btn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
            <?php
            unset($_SESSION["success_message"]);
            unset($_SESSION["success_username"]);
            ?>
        <?php endif; ?>

        // Error Alert
        <?php if (isset($_SESSION["error_message"])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?= addslashes($_SESSION["error_message"]) ?>',
                confirmButtonColor: '#ff6600',
                background: '#1a1a1a',
                color: '#fff',
                customClass: {
                    popup: 'swal-dark-theme',
                    confirmButton: 'swal-confirm-btn'
                }
            });
            <?php unset($_SESSION["error_message"]); ?>
        <?php endif; ?>
    </script>
</body>

</html>