document.addEventListener("DOMContentLoaded", () => {
    loadNotifications();

    // Toggle dropdown
    document.getElementById("notif-bell").onclick = () => {
        document.getElementById("notif-dropdown").classList.toggle("show");
    };
});

function loadNotifications() {
    fetch("../backend/notifications/get_player_notifications.php")
        .then(res => res.json())
        .then(data => {
            document.getElementById("notif-count").textContent = data.unread;

            let list = document.getElementById("notif-list");
            list.innerHTML = "";

            if (data.notifications.length === 0) {
                list.innerHTML = "<li>No notifications</li>";
                return;
            }

            data.notifications.forEach(n => {
                let li = document.createElement("li");
                li.innerHTML = `
                    <strong>${n.title}</strong><br>
                    <small>${n.created_at}</small>
                `;
                list.appendChild(li);
            });
        });
}
