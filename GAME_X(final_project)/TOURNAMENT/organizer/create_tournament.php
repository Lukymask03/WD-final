<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/auth_guard.php';

// Check authentication
checkAuth('organizer');

// ===== Lookup organizer_id from organizer_profiles =====
$stmtProfile = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
$stmtProfile->execute([$_SESSION['account_id']]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Organizer profile not found. Please complete your profile first.");
}

$organizer_id = $profile['organizer_id'];

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $max_teams = intval($_POST['max_teams'] ?? 0);
    $reg_start_date = trim($_POST['reg_start_date'] ?? '');
    $reg_end_date = trim($_POST['reg_end_date'] ?? '');
    $game = trim($_POST['game'] ?? '');

    // Convert to timestamps for validation
    $start_ts = strtotime($start_date);
    $end_ts = $end_date ? strtotime($end_date) : null;
    $reg_start_ts = strtotime($reg_start_date);
    $reg_deadline_ts = strtotime($reg_end_date);

    // Validation
    if (!$title || !$start_date || !$reg_start_date || !$reg_end_date || $max_teams <= 0 || !$game) {
        $error = "Please fill in all required fields correctly.";
    } elseif ($reg_start_ts > $start_ts) {
        $error = "Registration cannot start after the tournament start date.";
    } elseif ($reg_deadline_ts <= $reg_start_ts) {
        $error = "Registration deadline must be after the registration start date.";
    } elseif ($reg_deadline_ts >= $start_ts) {
        $error = "Registration deadline must be before the tournament start date.";
    } elseif ($end_ts && $start_ts > $end_ts) {
        $error = "Tournament start date cannot be after the end date.";
    }

    // Insert into DB
    if (empty($error)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO tournaments
                (organizer_id, title, description, start_date, end_date, max_teams, reg_start_date, reg_end_date, game, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')
            ");

            $stmt->execute([
                $organizer_id,
                $title,
                $description,
                $start_date . " 00:00:00",
                $end_ts ? $end_date . " 23:59:59" : null,
                $max_teams,
                $reg_start_date . " 00:00:00",
                $reg_end_date . " 23:59:59",
                $game
            ]);

            $success = "Tournament created successfully!";

            // Clear form values after success
            $title = $description = $start_date = $end_date = $reg_start_date = $reg_end_date = $game = '';
            $max_teams = 0;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), '1452') !== false) {
                $error = "Database error: Organizer profile not found in organizer_profiles table.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tournament - Game X</title>

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
    <?php include '../includes/organizer/organizer_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="org-main">
        <div class="org-form-container">
            <div class="org-form-card">
                <div class="org-form-header">
                    <h1 class="org-form-title"><i class="fas fa-plus-circle"></i> Create New Tournament</h1>
                    <p class="org-form-subtitle">Fill in the details below to create a new tournament</p>
                </div>

                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="org-alert org-alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php elseif (!empty($error)): ?>
                    <div class="org-alert org-alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Tournament Form -->
                <form action="" method="POST">
                    <div class="org-form-group">
                        <label for="title" class="org-form-label">Tournament Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="org-form-control" required
                            value="<?= htmlspecialchars($title ?? '') ?>"
                            placeholder="Enter tournament name">
                    </div>

                    <div class="org-form-group">
                        <label for="game" class="org-form-label">Game <span class="required">*</span></label>
                        <input type="text" id="game" name="game" class="org-form-control" required
                            value="<?= htmlspecialchars($game ?? '') ?>"
                            placeholder="e.g., Mobile Legends, Call of Duty, Valorant">
                    </div>

                    <div class="org-form-group">
                        <label for="description" class="org-form-label">Description</label>
                        <textarea id="description" name="description" class="org-form-control"
                            placeholder="Provide details about the tournament..."><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>

                    <div class="org-form-row">
                        <div class="org-form-group">
                            <label for="start_date" class="org-form-label">Tournament Start Date <span class="required">*</span></label>
                            <input type="date" id="start_date" name="start_date" class="org-form-control" required
                                value="<?= htmlspecialchars($start_date ?? '') ?>">
                        </div>

                        <div class="org-form-group">
                            <label for="end_date" class="org-form-label">Tournament End Date</label>
                            <input type="date" id="end_date" name="end_date" class="org-form-control"
                                value="<?= htmlspecialchars($end_date ?? '') ?>">
                        </div>
                    </div>

                    <div class="org-form-row">
                        <div class="org-form-group">
                            <label for="reg_start_date" class="org-form-label">Registration Start <span class="required">*</span></label>
                            <input type="date" id="reg_start_date" name="reg_start_date" class="org-form-control" required
                                value="<?= htmlspecialchars($reg_start_date ?? '') ?>">
                        </div>

                        <div class="org-form-group">
                            <label for="reg_end_date" class="org-form-label">Registration Deadline <span class="required">*</span></label>
                            <input type="date" id="reg_end_date" name="reg_end_date" class="org-form-control" required
                                value="<?= htmlspecialchars($reg_end_date ?? '') ?>">
                        </div>
                    </div>

                    <div class="org-form-group">
                        <label for="max_teams" class="org-form-label">Maximum Teams <span class="required">*</span></label>
                        <input type="number" id="max_teams" name="max_teams" class="org-form-control" min="2" required
                            value="<?= htmlspecialchars($max_teams ?? '') ?>"
                            placeholder="Enter maximum number of teams">
                    </div>

                    <div class="org-form-actions">
                        <button type="submit" class="org-btn">
                            <i class="fas fa-save"></i> Create Tournament
                        </button>
                        <a href="view_tournaments.php" class="org-btn org-btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>

</html>