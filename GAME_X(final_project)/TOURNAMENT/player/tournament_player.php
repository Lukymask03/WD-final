<?php
// ============================================
//  my_registrations.php
//  Player-side: View My Tournament Registrations
// ============================================

require_once(__DIR__ . '/../backend/config.php');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Require login as player
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'player') {
  header("Location: ../auth/login.php");
  exit;
}

$account_id = $_SESSION['account_id'];

// =============================
// Fetch player's registered tournaments
// =============================
$query = "
    SELECT 
        t.tournament_id,
        t.title AS tournament_title,
        t.description AS tournament_description,
        t.start_date,
        t.end_date,
        t.status AS tournament_status,
        r.status AS registration_status
    FROM registrations r
    INNER JOIN tournaments t ON r.tournament_id = t.tournament_id
    WHERE r.account_id = :account_id
    ORDER BY t.start_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
$stmt->execute();
$my_tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tournament Registrations | GAME X</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/organizer_modern.css">
  <link rel="stylesheet" href="../assets/css/gaming_modern.css">
  <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body>
  <?php include '../includes/player/player_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="org-main">
    <!-- Gaming Hero Section -->
    <section class="gaming-hero" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
      <div class="gaming-hero__bg"></div>
      <div class="gaming-hero__content">
        <div class="gaming-hero__badge">
          <i class="fas fa-trophy"></i>
          <span>My Tournaments</span>
        </div>
        <h1 class="gaming-hero__title">Tournament Registrations</h1>
        <p class="gaming-hero__subtitle">View and manage your tournament registrations</p>
      </div>
    </section>

    <!-- Tournament Cards Grid -->
    <div class="content-section">
      <?php if (count($my_tournaments) > 0): ?>
        <div class="tournament-grid">
          <?php foreach ($my_tournaments as $t): ?>
            <div class="tournament-card">
              <div class="tournament-card__header">
                <h3 class="tournament-card__title"><?= htmlspecialchars($t['tournament_title']) ?></h3>
                <span class="status-badge status-badge--<?= $t['registration_status'] === 'accepted' ? 'success' : ($t['registration_status'] === 'pending' ? 'warning' : 'danger') ?>">
                  <?= ucfirst($t['registration_status']) ?>
                </span>
              </div>

              <p class="tournament-card__desc"><?= htmlspecialchars($t['tournament_description']) ?></p>

              <div class="tournament-card__info">
                <div class="info-item info-item--blue">
                  <div class="info-item__icon">
                    <i class="fas fa-calendar-day"></i>
                  </div>
                  <div class="info-item__content">
                    <div class="info-item__label">Start Date</div>
                    <div class="info-item__value"><?= date('F d, Y', strtotime($t['start_date'])) ?></div>
                  </div>
                </div>

                <div class="info-item info-item--red">
                  <div class="info-item__icon">
                    <i class="fas fa-flag-checkered"></i>
                  </div>
                  <div class="info-item__content">
                    <div class="info-item__label">End Date</div>
                    <div class="info-item__value"><?= date('F d, Y', strtotime($t['end_date'])) ?></div>
                  </div>
                </div>

                <div class="info-item info-item--purple">
                  <div class="info-item__icon">
                    <i class="fas fa-info-circle"></i>
                  </div>
                  <div class="info-item__content">
                    <div class="info-item__label">Status</div>
                    <div class="info-item__value"><?= ucfirst($t['tournament_status']) ?></div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
          <div class="empty-state__icon">
            <i class="fas fa-trophy"></i>
          </div>
          <h3 class="empty-state__title">No Tournament Registrations</h3>
          <p class="empty-state__message">You haven't registered for any tournaments yet. Join now to compete!</p>
          <a href="register_tournament.php" class="btn-gaming btn-gaming--primary">
            <i class="fas fa-clipboard-list"></i> Register for Tournament
          </a>
        </div>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>