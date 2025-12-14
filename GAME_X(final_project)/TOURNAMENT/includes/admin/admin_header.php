<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .top-header {
        width: calc(100% - 270px);
        height: 65px;
        background: #ffffff;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 25px;
        position: fixed;
        top: 0;
        left: 270px;
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

    .notification-wrapper {
        position: relative;
    }

    .notif-bell {
        cursor: pointer;
        font-size: 20px;
        position: relative;
        color: #333;
        transition: all 0.3s ease;
        padding: 8px;
        border-radius: 50%;
    }

    .notif-bell:hover {
        background: #f0f0f0;
        color: #ff6600;
    }

    .notif-count {
        background: #ff3300;
        color: #fff;
        padding: 2px 6px;
        font-size: 11px;
        border-radius: 10px;
        position: absolute;
        top: 2px;
        right: 2px;
        min-width: 18px;
        text-align: center;
        font-weight: 600;
        display: none;
    }

    .notif-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 45px;
        background: white;
        width: 350px;
        max-height: 450px;
        border: 1px solid #ddd;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        z-index: 1000;
        animation: slideDown 0.2s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notif-dropdown h4 {
        margin: 0;
        padding: 15px 20px;
        background: #ff6600;
        color: white;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #notif-list {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 380px;
        overflow-y: auto;
    }

    #notif-list::-webkit-scrollbar {
        width: 6px;
    }

    #notif-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    #notif-list::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }

    #notif-list::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    #notif-list li {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #notif-list li:hover {
        background: #f9f9f9;
    }

    #notif-list li:last-child {
        border-bottom: none;
    }

    #notif-list li.unread {
        background: #fff8f0;
    }

    .logout-btn {
        padding: 8px 20px;
        background: #ff3300;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .logout-btn:hover {
        background: #cc2900;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(255, 51, 0, 0.3);
    }

    body {
        padding-top: 70px;
    }

    body.dark-mode .top-header {
        background: #1a1a1a;
        border-bottom-color: #333;
    }

    body.dark-mode .header-left h2 {
        color: #ff8840;
    }

    body.dark-mode .notif-bell {
        color: #e0e0e0;
    }

    body.dark-mode .notif-bell:hover {
        background: #2a2a2a;
        color: #ff8840;
    }

    body.dark-mode .notif-dropdown {
        background: #2a2a2a;
        border-color: #444;
    }

    body.dark-mode .notif-dropdown h4 {
        background: #ff6600;
    }

    body.dark-mode #notif-list li {
        border-bottom-color: #333;
        color: #e0e0e0;
    }

    body.dark-mode #notif-list li:hover {
        background: #333;
    }

    body.dark-mode #notif-list li.unread {
        background: #2d2416;
    }
</style>

<header class="top-header">
    <div class="header-left">
        <h2><i class="fas fa-shield-halved"></i> Admin Panel</h2>
    </div>

    <div class="header-right">

        <!-- Notification Bell -->
        <div class="notification-wrapper">
            <div id="notif-bell" class="notif-bell">
                <i class="fa-solid fa-bell"></i>
                <span id="notif-count" class="notif-count">0</span>
            </div>

            <!-- Dropdown -->
            <div id="notif-dropdown" class="notif-dropdown">
                <h4>
                    <span><i class="fas fa-bell"></i> Notifications</span>
                </h4>
                <ul id="notif-list">
                    <li style="text-align: center; padding: 30px; color: #999;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i>
                        <p>Loading...</p>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Logout -->
        <a href="../auth/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</header>

<!-- Include the notification JavaScript - INLINE VERSION -->
<script>
// Admin Notification System - INLINE
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notification system initializing...');
    
    const notifBell = document.getElementById('notif-bell');
    const notifDropdown = document.getElementById('notif-dropdown');
    const notifCount = document.getElementById('notif-count');
    const notifList = document.getElementById('notif-list');

    console.log('Bell element:', notifBell);
    console.log('Dropdown element:', notifDropdown);

    // Toggle notification dropdown
    if (notifBell) {
        notifBell.addEventListener('click', function(e) {
            console.log('Bell clicked!');
            e.stopPropagation();
            const isVisible = notifDropdown.style.display === 'block';
            notifDropdown.style.display = isVisible ? 'none' : 'block';
            console.log('Dropdown display:', notifDropdown.style.display);
            
            // Mark as read when opened
            if (!isVisible) {
                markNotificationsAsRead();
            }
        });
    } else {
        console.error('Notification bell element not found!');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (notifDropdown && !notifDropdown.contains(e.target) && !notifBell.contains(e.target)) {
            notifDropdown.style.display = 'none';
        }
    });

    // Fetch notifications on page load
    fetchNotifications();

    // Auto-refresh notifications every 30 seconds
    setInterval(fetchNotifications, 30000);

    // Fetch notifications from server
    function fetchNotifications() {
        console.log('Fetching notifications...');
        fetch('../backend/admin/get_notifications.php')
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Notifications data:', data);
                if (data.success) {
                    updateNotifications(data.notifications);
                    updateNotificationCount(data.unread_count);
                } else {
                    console.error('Error from server:', data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    }

    // Update notification list
    function updateNotifications(notifications) {
        if (!notifList) return;

        notifList.innerHTML = '';

        if (notifications.length === 0) {
            notifList.innerHTML = `
                <li style="padding: 20px; text-align: center; color: #999;">
                    <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>No notifications</p>
                </li>
            `;
            return;
        }

        notifications.forEach(notif => {
            const li = document.createElement('li');
            li.style.cursor = 'pointer';
            li.className = notif.is_read == 0 ? 'unread' : '';
            
            li.innerHTML = `
                <div style="display: flex; gap: 10px; align-items: start;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: ${notif.is_read == 0 ? '#ff6600' : '#ddd'}; margin-top: 6px; flex-shrink: 0;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 3px;">${notif.title}</div>
                        <div style="font-size: 13px; color: #666; margin-bottom: 5px;">${notif.message}</div>
                        <div style="font-size: 11px; color: #999;">
                            <i class="far fa-clock"></i> ${formatTime(notif.created_at)}
                        </div>
                    </div>
                </div>
            `;
            
            // Click to mark as read and navigate if has link
            li.addEventListener('click', function() {
                markAsRead(notif.notification_id);
                if (notif.link) {
                    window.location.href = notif.link;
                }
            });
            
            notifList.appendChild(li);
        });
    }

    // Update notification count badge
    function updateNotificationCount(count) {
        if (!notifCount) return;
        
        console.log('Unread count:', count);
        if (count > 0) {
            notifCount.textContent = count > 99 ? '99+' : count;
            notifCount.style.display = 'block';
        } else {
            notifCount.style.display = 'none';
        }
    }

    // Mark single notification as read
    function markAsRead(notificationId) {
        fetch('../backend/admin/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Mark all notifications as read
    function markNotificationsAsRead() {
        fetch('../backend/admin/mark_all_notifications_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setTimeout(fetchNotifications, 500);
            }
        })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
        });
    }

    // Format time ago
    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
        
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
        });
    }
});
</script>