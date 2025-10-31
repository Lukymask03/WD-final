// === MOBILE MENU TOGGLE ===
const mobileMenuToggle = document.getElementById("mobileMenuToggle");
const navMenu = document.querySelector(".nav-menu");

if (mobileMenuToggle) {
  mobileMenuToggle.addEventListener("click", () => {
    navMenu.classList.toggle("active");
    const icon = mobileMenuToggle.querySelector("i");
    
    if (navMenu.classList.contains("active")) {
      icon.classList.remove("fa-bars");
      icon.classList.add("fa-times");
    } else {
      icon.classList.remove("fa-times");
      icon.classList.add("fa-bars");
    }
  });
}

// Close mobile menu when clicking outside
document.addEventListener("click", (e) => {
  if (navMenu && navMenu.classList.contains("active") && 
      !e.target.closest(".nav-menu") && 
      !e.target.closest(".mobile-menu-toggle")) {
    navMenu.classList.remove("active");
    const icon = mobileMenuToggle.querySelector("i");
    icon.classList.remove("fa-times");
    icon.classList.add("fa-bars");
  }
});

// === SLIDESHOW ===
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) { 
  showSlides(slideIndex += n); 
}

function currentSlide(n) { 
  showSlides(slideIndex = n); 
}

function showSlides(n) {
  const slides = document.getElementsByClassName("slide");
  const dots = document.getElementsByClassName("dot");
  
  if (n > slides.length) { slideIndex = 1; }
  if (n < 1) { slideIndex = slides.length; }
  
  for (let i = 0; i < slides.length; i++) { 
    slides[i].style.display = "none"; 
  }
  
  for (let i = 0; i < dots.length; i++) { 
    dots[i].classList.remove("active"); 
  }
  
  slides[slideIndex - 1].style.display = "block";
  dots[slideIndex - 1].classList.add("active");
}

// Auto-advance slideshow
setInterval(() => plusSlides(1), 6000);

// === SCROLL TO TOP BUTTON ===
const scrollToTopBtn = document.getElementById("scrollToTop");

if (scrollToTopBtn) {
  window.addEventListener("scroll", () => {
    if (window.pageYOffset > 300) {
      scrollToTopBtn.classList.add("visible");
    } else {
      scrollToTopBtn.classList.remove("visible");
    }
  });

  scrollToTopBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth"
    });
  });
}

// === INTERSECTION OBSERVER FOR ANIMATIONS ===
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px"
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = "1";
      entry.target.style.transform = "translateY(0)";
    }
  });
}, observerOptions);

// Observe all animated elements
document.addEventListener("DOMContentLoaded", () => {
  const animatedElements = document.querySelectorAll(".animate-fade-in, .feature-card, .news-card, .leaderboard-item");
  animatedElements.forEach(el => {
    el.style.opacity = "0";
    el.style.transform = "translateY(20px)";
    el.style.transition = "opacity 0.6s ease-out, transform 0.6s ease-out";
    observer.observe(el);
  });
});

// === SMOOTH SCROLL FOR ANCHOR LINKS ===
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    }
  });
});

// === NAVBAR SCROLL EFFECT ===
let lastScroll = 0;
const navbar = document.querySelector(".navbar");

window.addEventListener("scroll", () => {
  const currentScroll = window.pageYOffset;
  
  if (currentScroll > 100) {
    navbar.style.boxShadow = "0 4px 20px var(--shadow-lg)";
  } else {
    navbar.style.boxShadow = "0 2px 10px var(--shadow)";
  }
  
  lastScroll = currentScroll;
});
