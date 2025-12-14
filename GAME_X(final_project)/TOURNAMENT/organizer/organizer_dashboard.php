<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organizer Dashboard - Game X</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Modern Organizer CSS -->
  <link rel="stylesheet" href="../assets/css/organizer_modern.css">
</head>

<body>
  <?php
  session_start();
  require_once '../backend/db.php';
  require_once '../backend/helpers/auth_guard.php';

  // Check authentication
  checkAuth('organizer');

  // Get organizer account ID
  $account_id = $_SESSION['account_id'];

  // Get organizer profile and organizer_id
  try {
    $stmt = $conn->prepare("SELECT * FROM organizer_profiles WHERE account_id = ?");
    $stmt->execute([$account_id]);
    $organizer = $stmt->fetch();

    // Get the organizer_id for use in tournament queries
    $organizer_id = $organizer['organizer_id'] ?? null;

    // Create profile if doesn't exist
    if (!$organizer) {
      $stmt = $conn->prepare("INSERT INTO organizer_profiles (account_id, organization) VALUES (?, ?)");
      $stmt->execute([$account_id, $_SESSION['username'] . "'s Organization"]);

      $stmt = $conn->prepare("SELECT * FROM organizer_profiles WHERE account_id = ?");
      $stmt->execute([$account_id]);
      $organizer = $stmt->fetch();
    }
  } catch (PDOException $e) {
    $organizer = [
      'organization' => $_SESSION['username'] . "'s Organization",
      'contact_email' => $_SESSION['email'] ?? '',
      'website' => '',
      'description' => ''
    ];
  }

  // Get statistics
  try {
    // Total tournaments
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tournaments WHERE organizer_id = ?");
    $stmt->execute([$organizer_id]);
    $totalTournaments = $stmt->fetchColumn();

    // Active tournaments
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tournaments WHERE organizer_id = ? AND status = 'active'");
    $stmt->execute([$organizer_id]);
    $activeTournaments = $stmt->fetchColumn();

    // Completed tournaments
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tournaments WHERE organizer_id = ? AND status = 'completed'");
    $stmt->execute([$organizer_id]);
    $completedTournaments = $stmt->fetchColumn();

    // Total teams across all tournaments
    try {
      $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT tr.team_id) 
                FROM tournament_registrations tr
                JOIN tournaments t ON tr.tournament_id = t.tournament_id
                WHERE t.organizer_id = ?
            ");
      $stmt->execute([$organizer_id]);
      $totalTeams = $stmt->fetchColumn();
    } catch (PDOException $e) {
      $totalTeams = 0;
    }

    // Recent tournaments
    $stmt = $conn->prepare("
            SELECT tournament_id, title, start_date, end_date, status, max_teams, game
            FROM tournaments
            WHERE organizer_id = ?
            ORDER BY tournament_id DESC
            LIMIT 5
        ");
    $stmt->execute([$organizer_id]);
    $recentTournaments = $stmt->fetchAll();
  } catch (PDOException $e) {
    $totalTournaments = 0;
    $activeTournaments = 0;
    $completedTournaments = 0;
    $totalTeams = 0;
    $recentTournaments = [];
  }

  // Include sidebar
  include '../includes/organizer/organizer_sidebar.php';
  ?>

  <!-- Main Content -->
  <main class="org-main">
    <!-- Hero Welcome Section -->
    <section class="org-hero">
      <div class="org-hero-content">
        <div class="org-hero-badge">
          <i class="fas fa-crown"></i>
          Organizer Dashboard
        </div>
        <h1>Welcome back, <?php echo htmlspecialchars($organizer['organization'] ?? $_SESSION['username']); ?>! 👋</h1>
        <p>Manage your tournaments, track performance, and oversee all your competitive gaming events from one central hub.</p>
      </div>
    </section>

    <!-- Statistics Cards -->
    <section class="org-stats-grid">
      <div class="org-stat-card" style="animation-delay: 0.1s;">
        <div class="org-stat-icon">
          <i class="fas fa-trophy"></i>
        </div>
        <div class="org-stat-value"><?php echo $totalTournaments; ?></div>
        <div class="org-stat-label">Total Tournaments</div>
      </div>

      <div class="org-stat-card" style="animation-delay: 0.2s;">
        <div class="org-stat-icon">
          <i class="fas fa-play-circle"></i>
        </div>
        <div class="org-stat-value"><?php echo $activeTournaments; ?></div>
        <div class="org-stat-label">Active Tournaments</div>
      </div>

      <div class="org-stat-card" style="animation-delay: 0.3s;">
        <div class="org-stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="org-stat-value"><?php echo $completedTournaments; ?></div>
        <div class="org-stat-label">Completed Tournaments</div>
      </div>

      <div class="org-stat-card" style="animation-delay: 0.4s;">
        <div class="org-stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="org-stat-value"><?php echo $totalTeams; ?></div>
        <div class="org-stat-label">Total Teams</div>
      </div>
    </section>

    <!-- Quick Actions & Organization Info -->
    <section class="org-content-grid">
      <!-- Quick Actions -->
      <div class="org-card" style="animation-delay: 0.5s;">
        <div class="org-card-header">
          <div class="org-card-icon">
            <i class="fas fa-bolt"></i>
          </div>
          <h3 class="org-card-title">Quick Actions</h3>
        </div>
        <div class="org-card-content">
          <p>Get started with common tournament management tasks</p>
          <div class="org-btn-grid">
            <a href="create_tournament.php" class="org-btn">
              <i class="fas fa-plus-circle"></i>
              Create Tournament
            </a>
            <a href="view_tournaments.php" class="org-btn-secondary">
              <i class="fas fa-list"></i>
              View All
            </a>
            <a href="manage_brackets.php" class="org-btn-secondary">
              <i class="fas fa-project-diagram"></i>
              Manage Brackets
            </a>
            <a href="manage_matches.php" class="org-btn-secondary">
              <i class="fas fa-gamepad"></i>
              Manage Matches
            </a>
          </div>
        </div>
      </div>

      <!-- Organization Info -->
      <div class="org-card" style="animation-delay: 0.6s;">
        <div class="org-card-header">
          <div class="org-card-icon">
            <i class="fas fa-building"></i>
          </div>
          <h3 class="org-card-title">Organization Profile</h3>
        </div>
        <div class="org-card-content">
          <p><strong>Organization:</strong> <?php echo htmlspecialchars($organizer['organization'] ?? 'N/A'); ?></p>
          <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($organizer['contact_no'] ?? 'Not set'); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email'] ?? 'Not set'); ?></p>
          <p><strong>Website:</strong> <?php echo !empty($organizer['website']) ? htmlspecialchars($organizer['website']) : 'Not set'; ?></p>
        </div>
      </div>
    </section>

    <!-- Recent Tournaments -->
    <?php if (!empty($recentTournaments)): ?>
      <section class="org-table-container" style="animation-delay: 0.7s;">
        <div class="org-table-header">
          <h3 class="org-table-title">Recent Tournaments</h3>
          <a href="view_tournaments.php" class="org-btn">
            <i class="fas fa-eye"></i>
            View All
          </a>
        </div>
        <table class="org-data-table">
          <thead>
            <tr>
              <th>Tournament Name</th>
              <th>Game</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Max Teams</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentTournaments as $tournament): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($tournament['title']); ?></strong></td>
                <td><?php echo htmlspecialchars($tournament['game']); ?></td>
                <td><?php echo date('M d, Y', strtotime($tournament['start_date'])); ?></td>
                <td><?php echo date('M d, Y', strtotime($tournament['end_date'])); ?></td>
                <td><?php echo $tournament['max_teams']; ?></td>
                <td>
                  <?php
                  $badgeClass = 'org-badge-info';
                  if ($tournament['status'] == 'active') $badgeClass = 'org-badge-success';
                  elseif ($tournament['status'] == 'completed') $badgeClass = 'org-badge-warning';
                  ?>
                  <span class="org-badge <?php echo $badgeClass; ?>">
                    <?php echo ucfirst($tournament['status']); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    <?php else: ?>
      <section class="org-table-container" style="animation-delay: 0.7s;">
        <div class="org-empty-state">
          <i class="fas fa-trophy"></i>
          <h3>No Tournaments Yet</h3>
          <p>Create your first tournament to get started!</p>
          <a href="create_tournament.php" class="org-btn">
            <i class="fas fa-plus-circle"></i>
            Create Tournament
          </a>
        </div>
      </section>
    <?php endif; ?>
  </main>

  <script>
    // Add smooth animations on scroll
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    document.querySelectorAll('.org-card, .org-stat-card').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'all 0.6s ease-out';
      observer.observe(el);
    });
  </script>
</body>

</html>