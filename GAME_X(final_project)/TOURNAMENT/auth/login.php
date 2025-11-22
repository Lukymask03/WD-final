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
  <link rel="stylesheet" href="../assets/css/common.css">
  <link rel="stylesheet" href="../assets/css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="../assets/css/sweetalert2.min.css">
</head>

<body>
  <div class="login-container">
    <h1>Login</h1>
    <?php if (!empty($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

<form method="POST" action="">
    <input type="email" name="email" placeholder="Email" required>

   <div class="password-field">
  <input type="password" name="password" id="password" placeholder="Password" required>
  <span class="toggle-password" id="togglePassword">
     <i class="fa-solid fa-eye"></i>
  </span>
  <br>
</div>


    <button type="submit" class="login-btn">Login</button>

    <button type="button" class="create-account-btn" onclick="window.location.href='create_account.php'">
      Create Account
    </button>
</form>

  <script>
document.addEventListener("DOMContentLoaded", () => {
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", () => {
      const type = passwordInput.type === "password" ? "text" : "password";
      passwordInput.type = type;

      const icon = togglePassword.querySelector("i");
      icon.classList.toggle("fa-eye");
      icon.classList.toggle("fa-eye-slash");
    });
  }
});
</script>

  <script src="../assets/js/index.js"></script>
  <script src="../assets/js/darkmode_toggle.js"></script>


</body>

</html>