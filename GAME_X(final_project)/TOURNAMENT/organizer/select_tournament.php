<?php
session_start();
require_once "../backend/db.php";

if (!isset($_SESSION['account_id']) || $_SESSION['user_role'] !== 'organizer') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, name FROM tournaments WHERE organizer_id = ?");
$stmt->execute([$_SESSION['account_id']]);
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Tournament</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

</head>
<body>
  <!-- ==== NAVIGATION BAR ==== -->
  <header class="navbar">
      <?php include '../includes/organizer/organizer_sidebar.php'; ?>

      <div class="nav-actions">
           <a href="../auth/logout.php" class="btn">Logout</a>
        </div>
  </header>


    <main class="container">
        <h1>Select a Tournament to Manage Brackets</h1>
        <?php if ($tournaments): ?>
            <ul>
                <?php foreach ($tournaments as $t): ?>
                    <li>
                        <a href="manage_brackets.php?tournament_id=<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No tournaments found. <a href="create_tournament.php">Create one now.</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
