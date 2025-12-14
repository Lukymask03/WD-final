<?php
session_start();
require_once __DIR__ . "/../backend/db.php";
require_once __DIR__ . "/../backend/helpers/auth_guard.php";

checkAuth('organizer');

// Determine organizer_id from session or fetch from organizer_profiles
if (empty($_SESSION['organizer_id'])) {
    if (!empty($_SESSION['account_id'])) {
        try {
            // Try to fetch organizer profile
            $stmt = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
            $stmt->execute([$_SESSION['account_id']]);
            $organizer_profile = $stmt->fetch(PDO::FETCH_ASSOC);

            // If not found, create a new organizer profile automatically
            if (!$organizer_profile) {
                $stmtInsert = $conn->prepare("INSERT INTO organizer_profiles (account_id, organization, contact_no, website) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([$_SESSION['account_id'], 'My Organization', '09123456789', NULL]);

                // Fetch the newly created organizer_id
                $organizer_profile_id = $conn->lastInsertId();
                $_SESSION['organizer_id'] = $organizer_profile_id;
            } else {
                $_SESSION['organizer_id'] = $organizer_profile['organizer_id'];
            }
        } catch (PDOException $e) {
            die("Database error: " . htmlspecialchars($e->getMessage()));
        }
    } else {
        die("Organizer not logged in properly.");
    }
}

// Now organizer_id is guaranteed
$organizer_id = $_SESSION['organizer_id'];

// Fetch submitted reports for this organizer
try {
    $stmt = $conn->prepare("SELECT * FROM reports WHERE organizer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$organizer_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error while fetching reports: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Game X</title>

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
        <!-- Page Title -->
        <section class="org-hero">
            <div class="org-hero-content">
                <div class="org-hero-badge">
                    <i class="fas fa-file-alt"></i>
                    Reports
                </div>
                <h1>Your Submitted Reports 📋</h1>
                <p>View all reports you've submitted to the system administrators</p>
            </div>
        </section>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <div class="org-alert org-alert-success" style="margin-bottom: 30px;">
                <i class="fas fa-check-circle"></i>
                Report submitted successfully!
            </div>
        <?php endif; ?>

        <!-- Submit New Report Card -->
        <div class="org-card" style="margin-bottom: 40px;">
            <div class="org-card-header">
                <div class="org-card-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3 class="org-card-title">Submit New Report</h3>
            </div>
            <div class="org-card-content">
                <form action="submit_report.php" method="POST">
                    <div class="org-form-group">
                        <label for="subject" class="org-form-label">Subject <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" class="org-form-control" required
                            placeholder="Enter report subject">
                    </div>

                    <div class="org-form-group">
                        <label for="description" class="org-form-label">Description <span class="required">*</span></label>
                        <textarea id="description" name="description" class="org-form-control" rows="5" required
                            placeholder="Describe the issue or feedback..."></textarea>
                    </div>

                    <button type="submit" class="org-btn">
                        <i class="fas fa-paper-plane"></i> Submit Report
                    </button>
                </form>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="org-table-container">
            <div class="org-table-header">
                <h3 class="org-table-title"><i class="fas fa-list"></i> Report History</h3>
            </div>

            <?php if (!empty($reports)): ?>
                <table class="org-data-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Date Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($report['subject']) ?></strong></td>
                                <td><?= htmlspecialchars($report['description']) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($report['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="org-empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h3>No Reports Yet</h3>
                    <p>You haven't submitted any reports. Use the form above to submit your first report.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>