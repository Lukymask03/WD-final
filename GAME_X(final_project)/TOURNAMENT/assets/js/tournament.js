// === GAME NAME SHUFFLER === //
const gameNames = ['Valorant', 'League of Legends', 'CS:GO', 'Dota 2', 'Overwatch', 'Apex Legends', 'Fortnite', 'PUBG'];
let currentGameIndex = 0;

function shuffleGameName() {
  const gameNameElement = document.getElementById('gameName');
  if (gameNameElement) {
    gameNameElement.style.opacity = '0';
    gameNameElement.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
      currentGameIndex = (currentGameIndex + 1) % gameNames.length;
      gameNameElement.textContent = gameNames[currentGameIndex];
      gameNameElement.style.opacity = '1';
      gameNameElement.style.transform = 'translateY(0)';
    }, 300);
  }
}

// === SLIDESHOW === //
let slideIndex = 1;
let autoSlideTimer;

// Initialize slideshow and game name shuffler
document.addEventListener('DOMContentLoaded', function() {
  showSlides(slideIndex);
  startAutoSlide();
  
  // Start game name shuffling every 3 seconds
  setInterval(shuffleGameName, 3000);
});

function plusSlides(n) {
  clearTimeout(autoSlideTimer);
  showSlides(slideIndex += n);
  startAutoSlide();
}

function currentSlide(n) {
  clearTimeout(autoSlideTimer);
  showSlides(slideIndex = n);
  startAutoSlide();
}

function showSlides(n) {
  const slides = document.getElementsByClassName("slide");
  const dots = document.getElementsByClassName("dot");
  
  if (slides.length === 0) return;
  
  if (n > slides.length) slideIndex = 1;
  if (n < 1) slideIndex = slides.length;
  
  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
    slides[i].classList.remove("active");
  }
  
  for (let i = 0; i < dots.length; i++) {
    dots[i].classList.remove("active");
  }
  
  if (slides[slideIndex - 1]) {
    slides[slideIndex - 1].style.display = "block";
    slides[slideIndex - 1].classList.add("active");
  }
  
  if (dots[slideIndex - 1]) {
    dots[slideIndex - 1].classList.add("active");
  }
}

function startAutoSlide() {
  autoSlideTimer = setTimeout(() => {
    slideIndex++;
    showSlides(slideIndex);
    startAutoSlide();
  }, 6000);
}

// === LOGIN MODAL === //
function showLoginModal() {
  const modal = document.getElementById("loginModal");
  modal.style.display = "flex"; // flex so it's centered
}

function closeLoginModal() {
  const modal = document.getElementById("loginModal");
  modal.style.display = "none";
}

// Close modal when clicking outside of it
window.onclick = function (event) {
  const modal = document.getElementById("loginModal");
  if (event.target === modal) {
    modal.style.display = "none";
  }
};
