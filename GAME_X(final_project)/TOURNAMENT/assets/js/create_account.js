// ===============================
// Create Account JS
// ===============================

// Show fields based on selected role
document.addEventListener("DOMContentLoaded", function () {
    const roleSelect = document.getElementById("role");
    const playerFields = document.getElementById("player-fields");
    const organizerFields = document.getElementById("organizer-fields");

    // Hide both initially
    playerFields.style.display = "none";
    organizerFields.style.display = "none";

    // Listen for role changes
    roleSelect.addEventListener("change", function () {
        if (this.value === "player") {
            playerFields.style.display = "block";
            organizerFields.style.display = "none";
        } else if (this.value === "organizer") {
            playerFields.style.display = "none";
            organizerFields.style.display = "block";
        } else {
            playerFields.style.display = "none";
            organizerFields.style.display = "none";
        }
    });

    // ===============================
    // Password Toggle Visibility
    // ===============================
    document.querySelectorAll(".toggle-password").forEach(icon => {
        icon.addEventListener("click", () => {
            const targetId = icon.getAttribute("data-target");
            const passwordInput = document.getElementById(targetId);

            if (!passwordInput) return;

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.add("active");
                icon.textContent = "🙈"; // shows open-eye icon
            } else {
                passwordInput.type = "password";
                icon.classList.remove("active");
                icon.textContent = "👁️"; // shows closed-eye icon
            }
        });
    });
});

