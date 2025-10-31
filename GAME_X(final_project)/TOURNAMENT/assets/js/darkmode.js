// === DARK MODE TOGGLE SCRIPT ===

// Get references
const toggleBtn = document.getElementById("darkModeToggle");
const body = document.body;

// Check if dark mode was saved in localStorage
const savedMode = localStorage.getItem("theme");

// Apply saved mode (if any)
if (savedMode === "dark") {
  body.classList.add("dark-mode");
}

// When button is clicked
toggleBtn.addEventListener("click", () => {
  body.classList.toggle("dark-mode");

  // Save user choice
  if (body.classList.contains("dark-mode")) {
    localStorage.setItem("theme", "dark");
  } else {
    localStorage.setItem("theme", "light");
  }
});


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
