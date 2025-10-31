<?php
// Role-based authentication guard
require_once __DIR__ . "/log_activity.php"; // ✅ for logging

if (!function_exists('checkAuth')) {

    /**
     * Ensures that a user is logged in and optionally matches a required role.
     *
     * @param string|null $requiredRole Optional role to restrict page access
     */
    function checkAuth($requiredRole = null) {
        // Start session safely
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ✅ Prevent redirect loop by checking if already on login page
        $currentPage = basename($_SERVER['PHP_SELF']);
        $isLoginPage = ($currentPage === 'login.php' || $currentPage === 'login_form.php');

        // ✅ If not logged in, redirect to login_form.php (NOT login.php)
        if (!isset($_SESSION['account_id']) || !isset($_SESSION['role'])) {
            if (!$isLoginPage) {
                logActivity(null, "Unauthorized access attempt (no session)");
                header("Location: ../auth/login_form.php");
                exit;
            }
        }

        // ✅ Enforce role restriction if provided
        if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
            logActivity($_SESSION['account_id'], "Access denied: tried to access {$requiredRole} area");

            // Redirect to correct dashboard
            switch ($_SESSION['role']) {
                case 'admin':
                    header("Location: ../admin/admin_dashboard.php");
                    break;
                case 'organizer':
                    header("Location: ../organizer/organizer_dashboard.php");
                    break;
                case 'player':
                    header("Location: ../player/player_dashboard.php");
                    break;
                default:
                    header("Location: ../auth/login_form.php");
                    break;
            }
            exit;
        }
    }
}
?>

