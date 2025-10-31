let slideIndex = 0;
autoShowSlides();

// === SLIDESHOW === //
function autoShowSlides() {
  const slides = document.getElementsByClassName("slide");
  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  slideIndex++;
  if (slideIndex > slides.length) slideIndex = 1;
  slides[slideIndex - 1].style.display = "block";
  setTimeout(autoShowSlides, 3000);
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
