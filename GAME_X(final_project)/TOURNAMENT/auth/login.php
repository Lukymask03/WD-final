<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/log_activity.php'; // optional, for logging

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
                // ✅ Store session details
                $_SESSION["account_id"] = $user["account_id"];
                $_SESSION["username"]   = $user["username"];
                $_SESSION["role"]       = $user["role"];
                $_SESSION["email"]      = $user["email"];

                // ✅ Redirect based on role
                if ($user["role"] === "organizer") {
                    header("Location: ../organizer/organizer_dashboard.php");
                    exit;
                } elseif ($user["role"] === "player") {
                    header("Location: ../player/player_dashboard.php");
                    exit;
                } elseif ($user["role"] === "admin") {
                    header("Location: ../admin/admin_dashboard.php");
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
  <style>
    /* === BUTTON STYLES === */
    button, .btn-create-account {
      display: block;
      width: 100%;
      text-align: center;
      background-color: #ff5e00;
      color: #fff;
      padding: 12px 20px;
      margin-top: 12px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.3s ease;
    }

    button:hover, .btn-create-account:hover {
      background-color: #ff7b33;
    }

    /* === PASSWORD FIELD === */
    .password-field {
      position: relative;
      width: 100%;
    }

    .password-field input {
      width: 100%;
      padding: 12px 45px 12px 12px;
      border-radius: 8px;
      border: none;
      background: #1a1a1a;
      color: #fff;
      font-size: 1rem;
      outline: none;
    }

    .password-field input:focus {
      border: 2px solid var(--accent, #ff5e00);
    }

    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--accent, #ff5e00);
      font-size: 1.1rem;
      transition: color 0.3s ease;
    }

    .toggle-password:hover {
      color: var(--accent-hover, #ff7b33);
    }
  </style>
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
      </div>

      <button type="submit">Login</button>
    </form>

    <button onclick="window.location.href='create_account.php'">
      Create Account
    </button>
  </div>

  <script>
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");

    togglePassword.addEventListener("click", function () {
      const icon = this.querySelector("i");
      const isPassword = passwordInput.type === "password";
      passwordInput.type = isPassword ? "text" : "password";
      icon.classList.toggle("fa-eye");
      icon.classList.toggle("fa-eye-slash");
    });
  </script>

  <script src="../assets/js/index.js"></script>
  <script src="../assets/js/darkmode_toggle.js"></script>
</body>
</html>
