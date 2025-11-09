<?php
require_once __DIR__ . '/db.php';

// Start session only if none exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize input for security
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get logged-in user’s role
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Protect pages based on login and role
 */
function checkAccess($roles = null) {
    if (!isLoggedIn()) {
        header("Location: /TOURNAMENT/auth/login.php");
        exit();
    }

    if ($roles) {
        $userRole = getUserRole();
        if (is_array($roles)) {
            if (!in_array($userRole, $roles)) {
                denyAccess();
            }
        } elseif ($userRole !== $roles) {
            denyAccess();
        }
    }
}

/**
 * Deny Access
 */
function denyAccess() {
    header("HTTP/1.1 403 Forbidden");
    echo "<h2 style='color:red;text-align:center;margin-top:20%;'>Access Denied!</h2>";
    exit();
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Flash message helpers
 */
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

/**
 * ===============================
 * LOG USER ACTIONS (for audit_logs)
 * ===============================
 * Example: logAction($_SESSION['user_id'], 'Login', 'User logged in successfully');
 */
function logAction($user_id, $action, $details = null) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            INSERT INTO audit_logs (account_id, action, details, created_at)
            VALUES (:account_id, :action, :details, NOW())
        ");
        $stmt->execute([
            'account_id' => $user_id,
            'action' => $action,
            'details' => $details
        ]);
    } catch (PDOException $e) {
        // Optional: silently ignore or log error to a file
    }
}
?>
