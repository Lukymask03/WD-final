// === DARK MODE TOGGLE SCRIPT ===

// Get references
const toggleBtn = document.getElementById("darkModeToggle");
const body = document.body;

// Function to update button text and icon
function updateDarkModeButton(isDark) {
  if (!toggleBtn) return;
  
  const icon = toggleBtn.querySelector("i");
  const text = toggleBtn.querySelector("span");
  
  if (icon && text) {
    if (isDark) {
      icon.classList.remove("fa-moon");
      icon.classList.add("fa-sun");
      text.textContent = "Light Mode";
    } else {
      icon.classList.remove("fa-sun");
      icon.classList.add("fa-moon");
      text.textContent = "Dark Mode";
    }
  } else {
    // Fallback for buttons without icon/span structure
    toggleBtn.textContent = isDark ? "Light Mode" : "Dark Mode";
  }
}

// Check if dark mode was saved in localStorage
const savedMode = localStorage.getItem("theme");

// Apply saved mode (if any) immediately on page load
if (savedMode === "dark") {
  body.classList.add("dark-mode");
  updateDarkModeButton(true);
} else {
  updateDarkModeButton(false);
}

// When button is clicked
if (toggleBtn) {
  toggleBtn.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    const isDark = body.classList.contains("dark-mode");

    // Save user choice
    localStorage.setItem("theme", isDark ? "dark" : "light");
    
    // Update button appearance
    updateDarkModeButton(isDark);
  });
}


document.getElementById("togglePassword").addEventListener("click", function() {
    const pwd = document.getElementById("password");
    this.classList.toggle("fa-eye-slash");
    this.classList.toggle("fa-eye");
    pwd.type = pwd.type === "password" ? "text" : "password";
});

document.getElementById("toggleConfirm").addEventListener("click", function() {
    const conf = document.getElementById("confirm_password");
    this.classList.toggle("fa-eye-slash");
    this.classList.toggle("fa-eye");
    conf.type = conf.type === "password" ? "text" : "password";
});
