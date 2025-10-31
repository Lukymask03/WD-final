<?php
include 'backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | Game X Community</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/about.css">
    


</head>
<body>
<!-- Navigation Bar --> 
<header class="navbar">
     <div class="logo">
        <a href="index.php" style="text-decoration:none; color:inherit; display:flex; align-items:center;">
            <img src="assets/images/game_x_logo.png" alt="Game X Community" 
             style="height: 70px; vertical-align: middle; margin-right:12px;">
            <h1><span class="highlight-orange">GAME</span> <span class="highlight-red">X</span></h1>
        </a>
    </div>

    <nav>
        <a href="index.php">Home</a>
        <a href="about.php" class="active">About</a>
        <a href="tournament.php">Tournaments</a>
        <a href="contact.php">Contact</a>
    </nav>

    <div class="nav-actions">
       <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
        <a href="auth/login.php" class="cta-btn">Register / Login</a>
    </div>
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
    <h2>Meet the Team Behind Game X</h2>
    <div class="team-cards">
        <div class="team-card">
            <img src="assets/images/default.png" alt="Founder">
            <h4>Ramses Manalo</h4>
            <p>Founder & Lead Developer</p>
        </div>
        <div class="team-card">
            <img src="assets/images/default.png" alt="UI Designer">
            <h4>Ramses Manalo</h4>
            <p>UI/UX Designer</p>
        </div>
        <div class="team-card">
            <img src="assets/images/default.png" alt="Community Manager">
            <h4>Ramses Manalo</h4>
            <p>Community Manager</p>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <p>&copy; <?php echo date("Y"); ?> Game X Community. All rights reserved.</p>
</footer>
<script src="assets/js/about.js"></script>
<script src="assets/js/darkmode.js"></script>
<script src="assets/js/index.js"></script>
</body>
</html>
