<?php
session_start();
require_once __DIR__ . "/../backend/helpers/auth_guard.php";
checkAuth('organizer'); // ensures only organizers can access

// Include database connection
require_once __DIR__ . "/../backend/db.php"; // $conn is defined here

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
    <title>Organizer Reports</title> 
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f5f7;
    margin: 0;
    color: #1f2937;
}
.main-content {
    margin-left: 250px; /* space for sidebar */
    padding: 20px;
}
h2, h3 {
    color: #1f2937;
}
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
form input, form textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
form button {
    background-color: #f97316;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
}
form button:hover {
    background-color: #ea580c;
}
table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
table th, table td {
    padding: 12px 15px;
    text-align: left;
}
table th {
    background-color: #f97316;
    color: #fff;
}
table tr:nth-child(even) {
    background-color: #f4f4f4;
}
</style>
</head>
<body>
    <!-- ==== NAVIGATION BAR ==== -->
  <header class="navbar">
  <?php include '../includes/organizer/organizer_sidebar.php'; ?>

          <div class="nav-actions">
           <a href="../auth/logout.php" class="btn">Logout</a>
        </div>
  </header>

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
