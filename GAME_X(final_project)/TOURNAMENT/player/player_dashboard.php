<?php
session_start();
// Correct file pathing — only go one level up (no “tournament” folder)
require_once __DIR__ . '/../backend/helpers/auth_guard.php';
require_once __DIR__ . '/../backend/db.php';

// Protect page so only players can access
checkAuth('player');

// Get player info from session
$username = $_SESSION['username'] ?? 'Player';
$account_id = $_SESSION['account_id'];

// Initialize data arrays
$tournaments = [];
$teams = [];
$stats = [
  'active_tournaments' => 0,
  'my_teams' => 0,
  'wins' => 0,
  'championships' => 0
];

try {
  // Fetch player's registered tournaments
  $stmt = $conn->prepare("
    SELECT 
      t.tournament_id, 
      t.title, 
      t.status, 
      t.start_date,
      r.status as registration_status
    FROM registrations r
    INNER JOIN tournaments t ON r.tournament_id = t.tournament_id
    WHERE r.account_id = :account_id
    ORDER BY t.start_date DESC
    LIMIT 5
  ");
  $stmt->execute(['account_id' => $account_id]);
  $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Count active tournaments (registered and tournament is active)
  $stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM registrations r
    INNER JOIN tournaments t ON r.tournament_id = t.tournament_id
    WHERE r.account_id = :account_id 
    AND r.status = 'accepted'
    AND t.status IN ('upcoming', 'ongoing')
  ");
  $stmt->execute(['account_id' => $account_id]);
  $stats['active_tournaments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

  // Count teams the player is in
  $stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM team_members tm
    WHERE tm.account_id = :account_id
  ");
  $stmt->execute(['account_id' => $account_id]);
  $stats['my_teams'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

  // Count wins (if you have a match results table)
  // Assuming there's a match_results or tournament_results table
  // Adjust this query based on your actual schema
  $stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM match_results mr
    INNER JOIN matches m ON mr.match_id = m.match_id
    INNER JOIN team_members tm ON mr.team_id = tm.team_id
    WHERE tm.account_id = :account_id 
    AND mr.result = 'win'
  ");
  try {
    $stmt->execute(['account_id' => $account_id]);
    $stats['wins'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
  } catch (PDOException $e) {
    // Table might not exist yet
    $stats['wins'] = 0;
  }

  // Count championships (1st place tournament finishes)
  $stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM tournament_results tr
    INNER JOIN team_members tm ON tr.team_id = tm.team_id
    WHERE tm.account_id = :account_id 
    AND tr.placement = 1
  ");
  try {
    $stmt->execute(['account_id' => $account_id]);
    $stats['championships'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
  } catch (PDOException $e) {
    // Table might not exist yet
    $stats['championships'] = 0;
  }
} catch (PDOException $e) {
  error_log("Dashboard error: " . $e->getMessage());
  // Continue with empty data rather than dying
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Player Dashboard | GameX</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/organizer_modern.css">
  <link rel="stylesheet" href="../assets/css/gaming_modern.css">
  <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body style="background: var(--bg-primary); min-height: 100vh;">
  <?php include '../includes/player/player_sidebar.php'; ?>

  <main class="org-main">


    <!-- Gaming Hero Section -->
    <div class="gaming-hero">
      <div class="gaming-hero__orb gaming-hero__orb--primary"></div>
      <div class="gaming-hero__orb gaming-hero__orb--secondary"></div>

      <div class="gaming-hero__content">
        <div class="gaming-hero__badge">
          <div class="gaming-hero__badge-pulse"></div>
          <i class="fas fa-gamepad" style="color: var(--neon-primary); font-size: 1.1rem;"></i>
          <span style="color: var(--text-primary); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Player Dashboard</span>
        </div>

        <h1 class="gaming-hero__title">
          Welcome back, <?= htmlspecialchars($username) ?>!
        </h1>

        <p class="gaming-hero__subtitle">
          Track your tournaments, manage your teams, and compete for glory in the ultimate gaming arena!
        </p>
      </div>
    </div>

    <!-- Statistics Cards with Complex Design -->
    <section style="padding: 0 2rem; margin-bottom: 2rem;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; max-width: 1200px; margin: 0 auto;">
        <!-- Active Tournaments Card -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,94,0,0.2); border-radius: 20px; padding: 2rem; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(255,94,0,0.5)'; this.style.boxShadow='0 20px 60px rgba(255,94,0,0.2)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(255,94,0,0.2)'; this.style.boxShadow=''">
          <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle, rgba(255,94,0,0.1), transparent 70%); filter: blur(40px);"></div>
          <div style="position: relative; width: 60px; height: 60px; background: linear-gradient(135deg, #FF5E00, #FF7B33); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 32px rgba(255,94,0,0.4);">
            <i class="fas fa-trophy" style="color: white; font-size: 1.8rem;"></i>
          </div>
          <div style="color: #fafafa; font-size: 2.5rem; font-weight: 900; margin-bottom: 0.5rem; letter-spacing: -1px;"><?= $stats['active_tournaments'] ?></div>
          <div style="color: #71717a; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active Tournaments</div>
        </div>

        <!-- My Teams Card -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(139,92,246,0.2); border-radius: 20px; padding: 2rem; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(139,92,246,0.5)'; this.style.boxShadow='0 20px 60px rgba(139,92,246,0.2)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(139,92,246,0.2)'; this.style.boxShadow=''">
          <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle, rgba(139,92,246,0.1), transparent 70%); filter: blur(40px);"></div>
          <div style="position: relative; width: 60px; height: 60px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 32px rgba(139,92,246,0.4);">
            <i class="fas fa-users" style="color: white; font-size: 1.8rem;"></i>
          </div>
          <div style="color: #fafafa; font-size: 2.5rem; font-weight: 900; margin-bottom: 0.5rem; letter-spacing: -1px;"><?= $stats['my_teams'] ?></div>
          <div style="color: #71717a; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">My Teams</div>
        </div>

        <!-- Wins Card -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(59,130,246,0.2); border-radius: 20px; padding: 2rem; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(59,130,246,0.5)'; this.style.boxShadow='0 20px 60px rgba(59,130,246,0.2)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(59,130,246,0.2)'; this.style.boxShadow=''">
          <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle, rgba(59,130,246,0.1), transparent 70%); filter: blur(40px);"></div>
          <div style="position: relative; width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 32px rgba(59,130,246,0.4);">
            <i class="fas fa-medal" style="color: white; font-size: 1.8rem;"></i>
          </div>
          <div style="color: #fafafa; font-size: 2.5rem; font-weight: 900; margin-bottom: 0.5rem; letter-spacing: -1px;"><?= $stats['wins'] ?></div>
          <div style="color: #71717a; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Wins</div>
        </div>

        <!-- Championships Card -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(16,185,129,0.2); border-radius: 20px; padding: 2rem; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(16,185,129,0.5)'; this.style.boxShadow='0 20px 60px rgba(16,185,129,0.2)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(16,185,129,0.2)'; this.style.boxShadow=''">
          <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle, rgba(16,185,129,0.1), transparent 70%); filter: blur(40px);"></div>
          <div style="position: relative; width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 32px rgba(16,185,129,0.4);">
            <i class="fas fa-crown" style="color: white; font-size: 1.8rem;"></i>
          </div>
          <div style="color: #fafafa; font-size: 2.5rem; font-weight: 900; margin-bottom: 0.5rem; letter-spacing: -1px;"><?= $stats['championships'] ?></div>
          <div style="color: #71717a; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Championships</div>
        </div>
      </div>
    </section>

    <!-- Quick Actions & Player Info with Complex Design -->
    <section style="padding: 0 2rem 2rem; max-width: 1200px; margin: 0 auto;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem;">
        <!-- Quick Actions Card -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(255,94,0,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'">
          <!-- Background Effects -->
          <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,94,0,0.1), transparent 70%); filter: blur(60px);"></div>
          <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

          <!-- Header -->
          <div style="position: relative; background: linear-gradient(135deg, rgba(255,94,0,0.15) 0%, transparent 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div style="display: flex; align-items: center; gap: 1rem;">
              <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #FF5E00, #FF7B33); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(255,94,0,0.4);">
                <i class="fas fa-bolt" style="color: white; font-size: 1.3rem;"></i>
              </div>
              <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Quick Actions</h3>
            </div>
            <p style="margin: 0.75rem 0 0 0; color: #a1a1aa; font-size: 0.95rem;">Jump into action with these quick shortcuts</p>
          </div>

          <!-- Content -->
          <div style="position: relative; padding: 2rem; display: grid; gap: 1rem;">
            <a href="register_tournament.php" style="background: linear-gradient(135deg, #FF5E00, #FF7B33); border: none; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; color: white; text-decoration: none; font-weight: 700; font-size: 1rem; transition: all 0.3s; box-shadow: 0 8px 24px rgba(255,94,0,0.3);" onmouseover="this.style.transform='translateX(6px)'; this.style.boxShadow='0 12px 32px rgba(255,94,0,0.5)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 24px rgba(255,94,0,0.3)'">
              <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-plus-circle" style="font-size: 1.2rem;"></i>
              </div>
              <span>Register Tournament</span>
            </a>

            <a href="teams.php" style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; color: #fafafa; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.transform='translateX(6px)'; this.style.borderColor='rgba(139,92,246,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
              <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 16px rgba(139,92,246,0.3);">
                <i class="fas fa-users" style="font-size: 1.1rem; color: white;"></i>
              </div>
              <span>Create Team</span>
            </a>

            <a href="my_teams.php" style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; color: #fafafa; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.transform='translateX(6px)'; this.style.borderColor='rgba(59,130,246,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
              <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 16px rgba(59,130,246,0.3);">
                <i class="fas fa-shield-alt" style="font-size: 1.1rem; color: white;"></i>
              </div>
              <span>My Teams</span>
            </a>

            <a href="invitations.php" style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; color: #fafafa; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: all 0.3s;" onmouseover="this.style.transform='translateX(6px)'; this.style.borderColor='rgba(16,185,129,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
              <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 16px rgba(16,185,129,0.3);">
                <i class="fas fa-envelope" style="font-size: 1.1rem; color: white;"></i>
              </div>
              <span>Invitations</span>
            </a>
          </div>
        </div>

        <!-- My Tournaments Card -->
        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(139,92,246,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'">
          <!-- Background Effects -->
          <div style="position: absolute; top: -100px; left: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(139,92,246,0.1), transparent 70%); filter: blur(60px);"></div>
          <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

          <!-- Header -->
          <div style="position: relative; background: linear-gradient(135deg, rgba(139,92,246,0.15) 0%, transparent 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div style="display: flex; align-items: center; gap: 1rem;">
              <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(139,92,246,0.4);">
                <i class="fas fa-trophy" style="color: white; font-size: 1.3rem;"></i>
              </div>
              <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.5px;">My Tournaments</h3>
            </div>
          </div>

          <!-- Content -->
          <div style="position: relative; padding: 2rem;">
            <?php if (!empty($tournaments)): ?>
              <div style="display: grid; gap: 1rem;">
                <?php foreach ($tournaments as $t): ?>
                  <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 1.25rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(139,92,246,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                    <div style="color: #fafafa; font-weight: 700; font-size: 1.05rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($t['title']) ?></div>
                    <span style="display: inline-block; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 0.35rem 0.85rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($t['status']) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.2); border-radius: 12px; padding: 2rem; text-align: center;">
                <i class="fas fa-trophy" style="font-size: 3rem; color: #8b5cf6; opacity: 0.5; margin-bottom: 1rem; display: block;"></i>
                <p style="color: #a1a1aa; font-style: italic; margin: 0;">You haven't joined any tournaments yet. Register now to start competing!</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

  </main>

</body>
<!-- SweetAlert2 JS -->
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>
  <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
    Swal.fire({
      icon: 'success',
      title: 'Welcome Back! 🎮',
      html: '<p style="font-size: 1.1rem; margin-top: 10px;">Hello <strong><?= htmlspecialchars($username) ?></strong>! Ready to dominate the arena?</p>',
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false,
      background: '#1a1a1a',
      color: '#fff',
      iconColor: '#ff6600',
      customClass: {
        popup: 'swal-dark-theme'
      }
    });
    <?php unset($_SESSION['login_success']); ?>
  <?php endif; ?>
</script>

<script src="../assets/js/darkmode_toggle.js"></script>
<script src="../assets/js/index.js"></script>

</html>