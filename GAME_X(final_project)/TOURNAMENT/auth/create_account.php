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

    // Player fields
    $fullname = $_POST["fullname"] ?? null;
    $team = $_POST["team"] ?? null;
    $age = $_POST["age"] ?? null;

    // Organizer fields
    $organization = $_POST["organization"] ?? null;
    $contact_no = $_POST["contact_no"] ?? null;
    $website = $_POST["website"] ?? null;

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

        // Create organizer profile if role is organizer
        if ($role === 'organizer') {
            $orgStmt = $conn->prepare("
                INSERT INTO organizer_profiles (account_id, organization, contact_no, website)
                VALUES (:account_id, :organization, :contact_no, :website)
            ");
            $orgStmt->execute([
                ':account_id' => $account_id,
                ':organization' => $organization ?? 'Default Organization',
                ':contact_no' => $contact_no ?? '',
                ':website' => $website ?? ''
            ]);
        }

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
    <title>Create Account | GameX</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Auth CSS -->
    <link rel="stylesheet" href="../assets/css/auth_modern.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="../assets/css/sweetalert2.min.css">
</head>

<body>
    <div class="auth-container">
        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-trophy"></i>
            </div>
            <h1 class="auth-title">Join GameX</h1>
            <p class="auth-subtitle">Create your account and start competing</p>
        </div>

        <!-- Create Account Form -->
        <form action="create_account.php" method="POST" class="auth-form">
            <div class="auth-input-group">
                <label class="auth-label" for="username">Username</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="auth-input"
                    placeholder="Choose a username"
                    required>
            </div>

            <div class="auth-input-group">
                <label class="auth-label" for="email">Email Address</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="auth-input"
                    placeholder="Enter your email"
                    required>
            </div>

            <div class="auth-input-group">
                <label class="auth-label" for="password">Password</label>
                <div class="auth-password-wrapper">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="auth-input"
                        placeholder="Create a strong password"
                        required>
                    <i class="fas fa-eye auth-password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <div class="auth-input-group">
                <label class="auth-label" for="role">Account Type</label>
                <select name="role" id="role" class="auth-select" required>
                    <option value="">Select your role</option>
                    <option value="player">🎮 Player</option>
                    <option value="organizer">🏆 Tournament Organizer</option>
                </select>
            </div>

            <!-- PLAYER FIELDS -->
            <div id="player-fields" class="auth-conditional-fields">
                <div class="auth-input-group">
                    <label class="auth-label" for="fullname">Full Name</label>
                    <input
                        type="text"
                        name="fullname"
                        id="fullname"
                        class="auth-input"
                        placeholder="Enter your full name">
                </div>

                <div class="auth-input-group">
                    <label class="auth-label" for="team">Team/Clan (Optional)</label>
                    <input
                        type="text"
                        name="team"
                        id="team"
                        class="auth-input"
                        placeholder="Your team or clan name">
                </div>

                <div class="auth-input-group">
                    <label class="auth-label" for="age">Age</label>
                    <input
                        type="number"
                        name="age"
                        id="age"
                        class="auth-input"
                        placeholder="Your age"
                        min="13"
                        max="100">
                </div>
            </div>

            <!-- ORGANIZER FIELDS -->
            <div id="organizer-fields" class="auth-conditional-fields">
                <div class="auth-input-group">
                    <label class="auth-label" for="organization">Organization Name</label>
                    <input
                        type="text"
                        name="organization"
                        id="organization"
                        class="auth-input"
                        placeholder="Your organization name">
                </div>

                <div class="auth-input-group">
                    <label class="auth-label" for="contact_no">Contact Number</label>
                    <input
                        type="text"
                        name="contact_no"
                        id="contact_no"
                        class="auth-input"
                        placeholder="+63 XXX XXX XXXX">
                </div>

                <div class="auth-input-group">
                    <label class="auth-label" for="website">Website (Optional)</label>
                    <input
                        type="text"
                        name="website"
                        id="website"
                        class="auth-input"
                        placeholder="https://yourwebsite.com">
                </div>
            </div>

            <button type="submit" name="register" class="auth-btn auth-btn-primary">
                <i class="fas fa-user-plus"></i> Create Account
            </button>

            <button
                type="button"
                class="auth-btn auth-btn-secondary"
                onclick="window.location.href='login.php'">
                <i class="fas fa-arrow-left"></i> Back to Login
            </button>
        </form>

        <p class="auth-text-center text-small">
            By creating an account, you agree to our <a href="#" class="auth-link">Terms of Service</a>
        </p>
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
            playerFields.classList.remove("active");
            organizerFields.classList.remove("active");

            if (this.value === "player") {
                playerFields.classList.add("active");
            } else if (this.value === "organizer") {
                organizerFields.classList.add("active");
            }
        });

        // Success Alert
        <?php if (isset($_SESSION["success_message"])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Account Created! 🎮',
                html: '<p style="font-size: 1rem; margin-top: 10px;">Welcome to GameX, <strong><?= htmlspecialchars($_SESSION["success_username"] ?? "Player") ?></strong>!</p>',
                confirmButtonText: 'Login Now',
                confirmButtonColor: '#FF5E00',
                allowOutsideClick: false,
                background: '#0A0E27',
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
                confirmButtonColor: '#FF5E00',
                background: '#0A0E27',
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