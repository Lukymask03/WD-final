<?php
session_start();
require_once __DIR__ . "/../backend/db.php";
require_once __DIR__ . "/../backend/helpers/auth_guard.php";

checkAuth('organizer');

// Get organizer profile
$stmtProfile = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
$stmtProfile->execute([$_SESSION['account_id']]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Organizer profile not found.");
}

$organizer_id = $profile['organizer_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($subject) || empty($description)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO reports (organizer_id, subject, description, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$organizer_id, $subject, $description]);
            $success = "Report submitted successfully!";
        } catch (PDOException $e) {
            $error = "Error submitting report: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Report - Game X</title>

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

    <main class="org-main">
        <div class="org-form-container">
            <div class="org-form-card">
                <div class="org-form-header">
                    <h1 class="org-form-title"><i class="fas fa-exclamation-triangle"></i> Submit Report</h1>
                    <p class="org-form-subtitle">Report an issue or provide feedback</p>
                </div>

                <?php if ($success): ?>
                    <div class="org-alert org-alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="org-alert org-alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="org-form-group">
                        <label for="subject" class="org-form-label">Subject <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" class="org-form-control" required
                            placeholder="Enter report subject">
                    </div>

                    <div class="org-form-group">
                        <label for="description" class="org-form-label">Description <span class="required">*</span></label>
                        <textarea id="description" name="description" class="org-form-control" required
                            placeholder="Provide detailed description of the issue or feedback..." rows="8"></textarea>
                    </div>

                    <div class="org-form-actions">
                        <button type="submit" class="org-btn">
                            <i class="fas fa-paper-plane"></i> Submit Report
                        </button>
                        <a href="organizer_dashboard.php" class="org-btn org-btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>

</html>