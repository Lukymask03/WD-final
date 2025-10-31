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


// === SIMPLE SLIDESHOW ===
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n){ showSlides(slideIndex += n); }
function currentSlide(n){ showSlides(slideIndex = n); }

function showSlides(n){
  const slides = document.getElementsByClassName("slide");
  const dots = document.getElementsByClassName("dot");
  if(n > slides.length){ slideIndex = 1; }
  if(n < 1){ slideIndex = slides.length; }
  for(let i=0;i<slides.length;i++){ slides[i].style.display="none"; }
  for(let i=0;i<dots.length;i++){ dots[i].classList.remove("active"); }
  slides[slideIndex-1].style.display="block";
  dots[slideIndex-1].classList.add("active");
}
setInterval(()=>plusSlides(1),6000);
