<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/log_activity.php";

// Log activity *before* destroying session
if (isset($_SESSION['account_id']) && isset($_SESSION['role'])) {
    logActivity($conn, $_SESSION['account_id'], "Logout ({$_SESSION['role']})");
}

// Unset all session variables
$_SESSION = [];

// Destroy the session completely
session_destroy();

// Optional: clear session cookie (extra security)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect user back to login page
header("Location: login.php");
exit;
?>
