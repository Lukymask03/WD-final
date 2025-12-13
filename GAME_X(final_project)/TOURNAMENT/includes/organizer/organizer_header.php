//organizer_header.php

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Block access if not an organizer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .top-header {
        width: 100%;
        height: 65px;
        background: #ffffff;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 25px;
        position: fixed;
        top: 0;
        left: 270px; /* Same width as organizer sidebar */
        z-index: 999;
    }

    .header-left h2 {
        margin: 0;
        color: #ff6600;
        font-size: 22px;
        font-weight: bold;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .notif-bell {
        cursor: pointer;
        font-size: 20px;
        position: relative;
        color: #333;
    }

    .notif-count {
        background: red;
        color: #fff;
        padding: 2px 6px;
        font-size: 11px;
        border-radius: 50%;
        position: absolute;
        top: -5px;
        right: -10px;
    }

    .notif-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 40px;
        background: white;
        width: 280px;
        max-height: 320px;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow-y: auto;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 1000;
    }

    .notif-dropdown h4 {
        margin: 0;
        padding: 10px;
        background: #ff6600;
        color: white;
        font-size: 15px;
    }

    #notif-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    #notif-list li {
        padding: 10px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }

    #notif-list li:hover {
        background: #f7f7f7;
    }

    .logout-btn {
        padding: 8px 15px;
        background: #ff3300;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
    }

    body {
        padding-top: 70px; /* Prevent content overlap */
    }
</style>

<header class="top-header">
    <div class="header-left">
        <h2>Organizer Panel</h2>
    </div>

    <div class="header-right">

        <!-- 🔔 Notification Bell -->
        <div class="notification-wrapper" style="position: relative;">
            <div id="notif-bell" class="notif-bell">
                <i class="fa-solid fa-bell"></i>
                <span id="notif-count" class="notif-count">0</span>
            </div>

            <!-- Dropdown -->
            <div id="notif-dropdown" class="notif-dropdown">
                <h4>Notifications</h4>
                <ul id="notif-list"></ul>
            </div>
        </div>

        <!-- 🚪 Logout -->
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>
</header>
