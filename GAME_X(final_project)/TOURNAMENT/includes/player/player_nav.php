<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'player') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/player_nav.css">

<nav class="player-top-nav">

    <div class="nav-left">
        <h2 class="player-panel-title">Player Panel</h2>

    </div>

    <div class="nav-right">

        <!-- NOTIFICATION BELL -->
        <div class="notification-wrapper">

            <div id="notif-bell" class="notif-bell">
                <i class="fa-solid fa-bell"></i>
                <span id="notif-count" class="notif-count">0</span>
            </div>

            <!-- DROPDOWN -->
            <div id="notif-dropdown" class="notif-dropdown">
                <h4>Notifications</h4>
                <ul id="notif-list"></ul>
                <a href="player_notifications.php" class="view-all">View All</a>
            </div>
        </div>

        <!-- LOGOUT -->
        <a href="../auth/logout.php" class="logout-btn">Logout</a>



        <!-- Small burger inside sidebar to close it -->
        <button id="sidebarToggle" class="burger-btn">
           <i class="fas fa-bars"></i>
        </button>


    </div>

</nav>

<script src="../assets/js/player_notifications.js"></script>
