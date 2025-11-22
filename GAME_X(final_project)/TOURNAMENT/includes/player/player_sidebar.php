<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to players only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'player') {
    header("Location: ../auth/login.php");
    exit();
}

// Current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="../assets/css/admin_sidebar.css">
<link rel="stylesheet" href="../assets/css/player_sidebar_fix.css">

<style>
/* Sidebar default hidden */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100%;
    background: var(--bg-secondary);
    border-right: 2px solid var(--border);
    transform: translateX(-100%);
    transition: 0.3s ease-in-out;
    z-index: 3000;
}

/* Sidebar visible */
.sidebar.active {
    transform: translateX(0);
}

/* Burger button inside sidebar */
.sidebar-toggle {
    background: var(--bg-card);
    border: 2px solid var(--accent);
    color: var(--accent);
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 18px;
    cursor: pointer;
    margin-right: 10px;
}

.sidebar-toggle:hover {
    background: var(--accent);
    color: #fff;
}
</style>

<!-- Player Sidebar -->
<aside class="sidebar" id="playerSidebar">
    <div class="sidebar-header">

        <a href="player_dashboard.php" class="sidebar-logo">
            <img src="../assets/images/game_x_logo.png" alt="GameX Logo">
            <h1 class="sidebar-title">
                <span class="highlight-orange">GAME</span>
                <span class="highlight-red"> X</span>
            </h1>
        </a>
    </div>

    <nav class="sidebar-menu">

        <div class="menu-section">
            <div class="menu-section-title">Main</div>
            <a href="player_dashboard.php" 
            class="menu-item <?= $current_page === 'player_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Tournaments</div>
            <a href="register_tournament.php"
            class="menu-item <?= $current_page === 'register_tournament.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Register Tournament</span>
            </a>

            <a href="tournament_player.php"
            class="menu-item <?= $current_page === 'tournament_player.php' ? 'active' : '' ?>">
                <i class="fas fa-trophy"></i>
                <span>My Tournaments</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Teams</div>
            <a href="teams.php"
            class="menu-item <?= $current_page === 'teams.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>My Teams</span>
            </a>

            <a href="invitations.php"
            class="menu-item <?= $current_page === 'invitations.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i>
                <span>Invitations</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Support</div>
            <a href="player_contact.php"
            class="menu-item <?= $current_page === 'player_contact.php' ? 'active' : '' ?>">
                <i class="fas fa-headset"></i>
                <span>Support</span>
            </a>
        </div>

    </nav>
</aside>

<script>
// Toggle from NAV BAR burger button
document.addEventListener("DOMContentLoaded", () => {

    const sidebar = document.getElementById("playerSidebar");
    const navBurger = document.getElementById("sidebarToggle"); // from player_nav.php
    const closeBtn = document.getElementById("closeSidebar");

    if (navBurger) {
        navBurger.addEventListener("click", () => {
            sidebar.classList.toggle("active");
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            sidebar.classList.remove("active");
        });
    }
});
</script>
