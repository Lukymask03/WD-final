<?php
include 'backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Game X</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Navigation Bar -->
    <header class="navbar">
        <div class="logo">
            <a href="index.php" class="logo-link">
                <img src="assets/images/game_x_logo.png" alt="Game X Community" class="logo-img" />
                <h1><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h1>
            </a>
        </div>

        <nav class="nav-menu">
            <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="tournament.php" class="nav-link"><i class="fas fa-trophy"></i> Tournaments</a>
            <a href="about.php" class="nav-link active"><i class="fas fa-info-circle"></i> About</a>
            <a href="contact.php" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
        </nav>

        <div class="nav-actions">
            <button id="darkModeToggle" class="darkmode-btn">
                <i class="fas fa-moon"></i>
                <span>Dark Mode</span>
            </button>
            <a href="auth/login.php" class="cta-btn">
                <i class="fas fa-user"></i> Login
            </a>
        </div>

        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <!-- About Section -->
    <section class="about-section">
        <div class="about-header">
            <h1>About Game X Community</h1>
            <p>
                <strong>Game X Community</strong> is your digital arena for competitive gamers — a place where passion meets performance.
                Whether you’re a team leader, solo contender, or esports fan, this is where you connect, compete, and rise to the top.
            </p>
        </div>

        <div class="about-cards">
            <div class="about-card">
                <h3>Our Mission</h3>
                <p>To unite gamers through skill, teamwork, and fair play — creating a space where every match is legendary.</p>
            </div>
            <div class="about-card">
                <h3>Our Vision</h3>
                <p>To become the premier esports hub where tournaments, rankings, and community thrive under one digital roof.</p>
            </div>
            <div class="about-card">
                <h3> Our Community</h3>
                <p>Thousands of players are already competing on Game X. Join us and start your journey to gaming greatness.</p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-users"></i> Meet the Team Behind Game X</h2>
                <p class="section-subtitle">The passionate individuals dedicated to bringing you the best esports experience</p>
            </div>

            <div class="team-grid">
                <div class="team-card">
                    <div class="team-card-inner">
                        <div class="team-image">
                            <img src="assets/images/default.png" alt="Founder">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#" class="team-social-link"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" class="team-social-link"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="team-social-link"><i class="fab fa-github"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h4 class="team-name">Ramses Manalo</h4>
                            <p class="team-role">Founder & Lead Developer</p>
                            <p class="team-bio">Full-stack developer with a passion for creating seamless gaming experiences.</p>
                            <div class="team-skills">
                                <span class="skill-tag"><i class="fas fa-code"></i> Development</span>
                                <span class="skill-tag"><i class="fas fa-database"></i> Backend</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-card-inner">
                        <div class="team-image">
                            <img src="assets/images/default.png" alt="UI Designer">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#" class="team-social-link"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" class="team-social-link"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="team-social-link"><i class="fab fa-dribbble"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h4 class="team-name">Ramses Manalo</h4>
                            <p class="team-role">UI/UX Designer</p>
                            <p class="team-bio">Designer focused on creating beautiful and intuitive user interfaces.</p>
                            <div class="team-skills">
                                <span class="skill-tag"><i class="fas fa-paint-brush"></i> Design</span>
                                <span class="skill-tag"><i class="fas fa-mobile-alt"></i> UI/UX</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-card-inner">
                        <div class="team-image">
                            <img src="assets/images/default.png" alt="Community Manager">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#" class="team-social-link"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" class="team-social-link"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="team-social-link"><i class="fab fa-discord"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h4 class="team-name">Ramses Manalo</h4>
                            <p class="team-role">Community Manager</p>
                            <p class="team-bio">Dedicated to building and nurturing our growing gaming community.</p>
                            <div class="team-skills">
                                <span class="skill-tag"><i class="fas fa-comments"></i> Community</span>
                                <span class="skill-tag"><i class="fas fa-bullhorn"></i> Marketing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="assets/images/game_x_logo.png" alt="Game X" />
                        <h3><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h3>
                    </div>
                    <p class="footer-description">
                        The premier platform for esports tournaments and competitive gaming.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-discord"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitch"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                        <li><a href="tournament.php"><i class="fas fa-trophy"></i> Tournaments</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>For Players</h4>
                    <ul class="footer-links">
                        <li><a href="auth/create_account.php"><i class="fas fa-user-plus"></i> Register</a></li>
                        <li><a href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> FAQ</a></li>
                        <li><a href="#"><i class="fas fa-file-alt"></i> Rules</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="contact.php"><i class="fas fa-headset"></i> Help Center</a></li>
                        <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                        <li><a href="#"><i class="fas fa-gavel"></i> Terms of Service</a></li>
                        <li><a href="#"><i class="fas fa-bug"></i> Report Issue</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Game X Community. All rights reserved.</p>
                <p class="footer-made-with">Made with <i class="fas fa-heart"></i> for gamers</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="assets/js/about.js"></script>
    <script src="assets/js/darkmode.js"></script>
    <script src="assets/js/index.js"></script>
</body>

</html>