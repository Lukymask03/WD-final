<?php
require_once __DIR__ . "/../backend/helpers/auth_guard.php";
checkAuth('organizer');
require_once __DIR__ . "/../backend/config/db.php";

$organizer_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM reports WHERE organizer_id = ? ORDER BY created_at DESC");
$stmt->execute([$organizer_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Submit Report</h2>

    <?php if (isset($_GET['success'])): ?>
        <p style="color:green;">✅ Report submitted successfully!</p>
    <?php endif; ?>

    <form action="submit_report.php" method="POST">
        <label>Subject:</label>
        <input type="text" name="subject" required><br><br>

        <label>Description:</label><br>
        <textarea name="description" rows="5" required></textarea><br><br>

        <button type="submit">Submit Report</button>
    </form>

    <hr>

    <h3>Your Submitted Reports</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Subject</th>
            <th>Description</th>
            <th>Date</th>
        </tr>
        <?php foreach ($reports as $report): ?>
            <tr>
                <td><?= htmlspecialchars($report['subject']) ?></td>
                <td><?= htmlspecialchars($report['description']) ?></td>
                <td><?= htmlspecialchars($report['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
