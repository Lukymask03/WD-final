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
  <link rel="stylesheet" href="../assets/css/common.css">
  <link rel="stylesheet" href="../assets/css/tournament.css">

  <style>
    .tournament-section {
      padding: 2rem 5%;
    }

    .section-title {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: var(--accent);
    }

    .tournament-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .tournament-card {
      background: var(--bg-secondary);
      border-radius: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .tournament-card:hover {
      transform: translateY(-5px);
    }

    .tournament-img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .tournament-info {
      padding: 1.2rem;
    }

    .tournament-info h3 {
      margin: 0 0 0.5rem 0;
      color: var(--text-main);
    }

    .tournament-info p {
      color: var(--text-muted);
      margin-bottom: 0.8rem;
      font-size: 0.95rem;
    }

    .status-badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: bold;
      color: #fff;
    }

    .status-pending {
      background: #f0ad4e;
    }

    .status-approved {
      background: #28a745;
    }

    .status-rejected {
      background: #dc3545;
    }

    .no-data {
      text-align: center;
      padding: 3rem;
    }

    .no-data img {
      width: 180px;
      opacity: 0.8;
      margin-bottom: 1rem;
    }
  </style>
</head>

<body>

<!-- ========== NAVBAR ========== -->
<?php require_once "../includes/player/player_navbar.php"; ?>

<!-- ========== MAIN CONTENT ========== -->
<section class="tournament-section">
  <h2 class="section-title">My Tournament Registrations</h2>

  <?php if (count($my_tournaments) > 0): ?>
    <div class="tournament-grid">
      <?php foreach ($my_tournaments as $t): ?>
        <div class="tournament-card">
          <img src="../uploads/<?php echo htmlspecialchars($t['tournament_image']); ?>" alt="<?php echo htmlspecialchars($t['tournament_title']); ?>" class="tournament-img">
          <div class="tournament-info">
            <h3><?php echo htmlspecialchars($t['tournament_title']); ?></h3>
            <p><?php echo htmlspecialchars($t['tournament_description']); ?></p>
            <p><strong>Starts:</strong> <?php echo date('F d, Y', strtotime($t['start_date'])); ?></p>
            <p>
              <span class="status-badge status-<?php echo strtolower($t['registration_status']); ?>">
                <?php echo ucfirst($t['registration_status']); ?>
              </span>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="no-data">
      <img src="../assets/image/VALORANT.png" alt="No Registrations">
      <p>You haven’t registered for any tournaments yet.</p>
      <a href="register_tournament.php" class="join-btn">Join One Now</a>
    </div>
  <?php endif; ?>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
  <p>&copy; <?= date("Y"); ?> Game X Community. All rights reserved.</p>
</footer>

<!-- ===== SCRIPTS ===== -->
<script src="../assets/js/darkmode_toggle.js"></script>
<script src="../assets/js/index.js"></script>
</body>
</html>
