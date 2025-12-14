<?php
session_start();
require_once "../backend/db.php";

// Check if player is logged in
if (!isset($_SESSION['account_id'])) {
  header('Location: ../auth/login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - GameX</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/organizer_modern.css">
  <link rel="stylesheet" href="../assets/css/gaming_modern.css">
  <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body>
  <?php include '../includes/player/player_sidebar.php'; ?>

  <main class="org-main">
    <!-- Gaming Hero Section -->
    <section class="gaming-hero" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
      <div class="gaming-hero__bg"></div>
      <div class="gaming-hero__content">
        <div class="gaming-hero__badge">
          <i class="fas fa-envelope"></i>
          <span>Contact Us</span>
        </div>
        <h1 class="gaming-hero__title">Get in Touch</h1>
        <p class="gaming-hero__subtitle">We'd love to hear from you! Reach out for inquiries, collaborations, or feedback.</p>
      </div>
    </section>
    <!-- Contact Content -->
    <div class="content-section">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem;">
        <!-- Contact Info -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden;">
          <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>
          <div style="position: relative; padding: 2rem;">
            <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 800; margin: 0 0 1.5rem 0;">Contact Information</h3>
            <div style="display: grid; gap: 1rem;">
              <div style="background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 12px; padding: 1rem; display: flex; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-envelope" style="color: white;"></i></div>
                <div>
                  <div style="color: #a1a1aa; font-size: 0.75rem;">Email</div>
                  <div style="color: #fafafa; font-weight: 700;">support@gamexhub.com</div>
                </div>
              </div>
              <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); border-radius: 12px; padding: 1rem; display: flex; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-phone" style="color: white;"></i></div>
                <div>
                  <div style="color: #a1a1aa; font-size: 0.75rem;">Phone</div>
                  <div style="color: #fafafa; font-weight: 700;">+63 912 345 6789</div>
                </div>
              </div>
              <div style="background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.2); border-radius: 12px; padding: 1rem; display: flex; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-map-marker-alt" style="color: white;"></i></div>
                <div>
                  <div style="color: #a1a1aa; font-size: 0.75rem;">Location</div>
                  <div style="color: #fafafa; font-weight: 700;">Zamboanga City, Philippines</div>
                </div>
              </div>
            </div>
            <div style="margin-top: 1.5rem;">
              <p style="color: #a1a1aa; font-size: 0.85rem; margin-bottom: 1rem;">Follow Us:</p>
              <div style="display: grid; gap: 0.75rem;">
                <a href="https://www.facebook.com/GameXTournamentHub" target="_blank" style="background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 12px; padding: 1rem; color: #fafafa; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; transition: all 0.3s;" onmouseover="this.style.background='linear-gradient(135deg, #3b82f6, #2563eb)'" onmouseout="this.style.background='rgba(59,130,246,0.1)'"><i class="fab fa-facebook"></i> Facebook</a>
                <a href="https://discord.gg/GameXCommunity" target="_blank" style="background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.2); border-radius: 12px; padding: 1rem; color: #fafafa; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; transition: all 0.3s;" onmouseover="this.style.background='linear-gradient(135deg, #8b5cf6, #7c3aed)'" onmouseout="this.style.background='rgba(139,92,246,0.1)'"><i class="fab fa-discord"></i> Discord</a>
                <a href="https://twitter.com/GameXHub" target="_blank" style="background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 12px; padding: 1rem; color: #fafafa; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; transition: all 0.3s;" onmouseover="this.style.background='linear-gradient(135deg, #3b82f6, #2563eb)'" onmouseout="this.style.background='rgba(59,130,246,0.1)'"><i class="fab fa-twitter"></i> Twitter</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Feedback Form -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden;">
          <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>
          <div style="position: relative; padding: 2rem;">
            <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 800; margin: 0 0 0.5rem 0;">Send Feedback</h3>
            <p style="color: #a1a1aa; font-size: 0.95rem; margin: 0 0 1.5rem 0;">We value your thoughts about GameX.</p>

            <?php if (isset($_GET['success'])): ?>
              <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; display: flex; gap: 0.75rem;"><i class="fas fa-check-circle"></i> Message sent successfully!</div>
            <?php elseif (isset($_GET['error'])): ?>
              <div style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; display: flex; gap: 0.75rem;"><i class="fas fa-exclamation-circle"></i> Please enter a message.</div>
            <?php endif; ?>

            <form action="player_feedback_submit.php" method="POST" style="display: grid; gap: 1.5rem;">
              <div>
                <label style="color: #fafafa; font-weight: 600; margin-bottom: 0.5rem; display: block;"><i class="fas fa-comment-dots" style="color: #8b5cf6;"></i> Your Feedback</label>
                <textarea name="message" placeholder="Share your thoughts..." required style="width: 100%; min-height: 200px; background: rgba(39,39,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; color: #fafafa; resize: vertical;"></textarea>
              </div>
              <button type="submit" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; border-radius: 12px; padding: 1.1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 8px 24px rgba(139,92,246,0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'"><i class="fas fa-paper-plane"></i> Send Feedback</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <footer style="background: linear-gradient(135deg, #18181b, #1a1a1d); border-top: 1px solid rgba(255,255,255,0.05); padding: 2rem; text-align: center; margin-top: 3rem;">
      <p style="margin: 0; color: #71717a; font-size: 0.875rem;">&copy; <?php echo date('Y'); ?> Game X Community. All rights reserved.</p>
    </footer>
  </main>

  <!-- ===== SCRIPTS ===== -->
  <script src='../assets/js/darkmode.js'></script>
  <script src='../assets/js/index.js'></script>

</body>

</html>