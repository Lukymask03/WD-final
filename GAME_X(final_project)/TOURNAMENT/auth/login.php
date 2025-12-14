<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/log_activity.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);

  if (empty($email) || empty($password)) {
    $error = "Please enter both email and password.";
  } else {
    try {
      $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user["password"])) {
        $_SESSION["account_id"] = $user["account_id"];
        $_SESSION["username"]   = $user["username"];
        $_SESSION["role"]       = $user["role"];
        $_SESSION["email"]      = $user["email"];
        $_SESSION["is_admin"]   = $user["is_admin"];
        $_SESSION["login_success"] = true;

        // Check if user is admin (is_admin = 1)
        if ($user["is_admin"] == 1) {
          header("Location: ../admin/admin_dashboard.php");
          exit;
        } elseif ($user["role"] === "organizer") {
          header("Location: ../organizer/organizer_dashboard.php");
          exit;
        } elseif ($user["role"] === "player") {
          header("Location: ../player/player_dashboard.php");
          exit;
        } else {
          $error = "Invalid role assigned to this account.";
        }
      } else {
        $error = "Invalid email or password.";
      }
    } catch (PDOException $e) {
      die("Database error: " . htmlspecialchars($e->getMessage()));
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | GameX</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Modern Auth CSS -->
  <link rel="stylesheet" href="../assets/css/auth_modern.css">
</head>

<body>
  <div class="auth-container">
    <!-- Header -->
    <div class="auth-header">
      <div class="auth-logo">
        <i class="fas fa-gamepad"></i>
      </div>
      <h1 class="auth-title">Welcome Back</h1>
      <p class="auth-subtitle">Login to your GameX account</p>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($error)): ?>
      <div class="auth-alert auth-alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="" class="auth-form">
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
            placeholder="Enter your password"
            required>
          <i class="fas fa-eye auth-password-toggle" id="togglePassword"></i>
        </div>
      </div>

      <button type="submit" class="auth-btn auth-btn-primary">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>

      <div class="auth-divider">OR</div>

      <button
        type="button"
        class="auth-btn auth-btn-secondary"
        onclick="window.location.href='create_account.php'">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <p class="auth-text-center">
      By logging in, you agree to our <a href="#" class="auth-link">Terms of Service</a>
    </p>
  </div>

  <script>
    // Password toggle functionality
    document.addEventListener("DOMContentLoaded", () => {
      const togglePassword = document.getElementById("togglePassword");
      const passwordInput = document.getElementById("password");

      if (togglePassword && passwordInput) {
        togglePassword.addEventListener("click", () => {
          const type = passwordInput.type === "password" ? "text" : "password";
          passwordInput.type = type;

          togglePassword.classList.toggle("fa-eye");
          togglePassword.classList.toggle("fa-eye-slash");
        });
      }
    });
  </script>
</body>

</html>