<?php 
session_start();
require_once __DIR__ . '/../backend/db.php';

// Only allow organizers
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'organizer') {
    die("Unauthorized access.");
}

// ===== Lookup organizer_id from organizer_profiles =====
$stmtProfile = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
$stmtProfile->execute([$_SESSION['account_id']]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Organizer profile not found. Please complete your profile first.");
}

$organizer_id = $profile['organizer_id']; // ✅ Correct organizer_id to use in tournaments table

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
    $reg_deadline = trim($_POST['reg_deadline'] ?? '');

    // Convert to timestamps for validation
    $start_ts = strtotime($start_date);
    $end_ts = $end_date ? strtotime($end_date) : null;
    $reg_start_ts = strtotime($reg_start_date);
    $reg_deadline_ts = strtotime($reg_deadline);

    // Validation
    if (!$title || !$start_date || !$reg_start_date || !$reg_deadline || $max_teams <= 0) {
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
                (organizer_id, title, description, start_date, end_date, max_teams, reg_start, reg_deadline, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open')
            ");

            $stmt->execute([
                $organizer_id,                // ✅ use correct organizer_id from organizer_profiles
                $title,
                $description,
                $start_date . " 00:00:00",
                $end_date ? $end_date . " 23:59:59" : null,
                $max_teams,
                $reg_start_date . " 00:00:00",
                $reg_deadline . " 23:59:59"
            ]);

            $success = "Tournament created successfully!";
        } catch (PDOException $e) {
            // Provide more descriptive error if FK fails
            if (strpos($e->getMessage(), '1452') !== false) {
                $error = "Database error: Organizer profile not found in organizer_profiles table.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Optional: Display success/error messages
if ($success) {
    echo "<p style='color:green;'>$success</p>";
}
if ($error) {
    echo "<p style='color:red;'>$error</p>";
}
?>




<!DOCTYPE html>
<html>
<head>
    <title>Create Tournament</title>
  <link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
/* ======================
   SIDEBAR OFFSET + TOPBAR
====================== */
.topbar {
  position: fixed;
  top: 0;
  left: 260px; /* match sidebar width */
  right: 0;
  height: 65px;
  background: #fff;
  border-bottom: 3px solid #ff6600;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 40px;
  z-index: 1000;
}

.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #ff6600;
  margin: 0;
}

.logout-btn {
  background: #ff6600;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.3s ease;
}

.logout-btn:hover {
  background: #e55a00;
}

/* ======================
   MAIN CONTENT AREA
====================== */
main.content-area {
  margin-left: 280px; /* leave space for sidebar */
  padding: 100px 60px 40px; /* add top padding for navbar */
  min-height: 100vh;
  background: #f4f5f7;
  display: flex;
  justify-content: center;
}

/* ======================
   FORM CARD
====================== */
.form-section {
  background: #fff;
  max-width: 900px;
  width: 100%;
  padding: 40px 50px;
  border-radius: 14px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.form-section h2 {
  text-align: center;
  color: #ff6600;
  margin-bottom: 30px;
  font-weight: 700;
}

/* ======================
   MESSAGES
====================== */
.message {
  text-align: center;
  margin-bottom: 15px;
  font-weight: bold;
}
.message.success { color: green; }
.message.error { color: red; }

/* ======================
   FORM GRID
====================== */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px 30px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.full-width {
  grid-column: span 2;
}

label {
  font-weight: 600;
  color: #333;
  margin-bottom: 6px;
}

input, textarea {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
  width: 100%;
}

input:focus, textarea:focus {
  border-color: #ff6600;
  outline: none;
  box-shadow: 0 0 5px rgba(255, 102, 0, 0.3);
}

/* ======================
   BUTTON
====================== */
.btn {
  width: 100%;
  padding: 12px;
  background: #ff6600;
  border: none;
  color: white;
  font-size: 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  margin-top: 25px;
  transition: background 0.3s;
}

.btn:hover {
  background: #e55a00;
}

/* ======================
   RESPONSIVE DESIGN
====================== */
@media (max-width: 768px) {
  .topbar {
    left: 0;
    padding: 0 20px;
  }

  main.content-area {
    margin-left: 0;
    padding: 90px 20px 40px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
</head>
<body>
  <!-- ==== SIDEBAR ==== -->
  <?php include '../includes/organizer/organizer_sidebar.php'; ?>
  <?php include "../includes/organizer/organizer_header.php"; ?>

  <!-- ==== TOP NAVBAR ==== -->
  <header class="topbar">
    <h1 class="page-title">Create Tournament</h1>
    <a href="../auth/logout.php" class="logout-btn">Logout</a>
  </header>

  <!-- ==== MAIN CONTENT ==== -->
<!-- ==== MAIN CONTENT ==== -->
<main class="content-area">
    <section class="form-section">

        <!-- ===== Feedback Messages ===== -->
        <?php if($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php elseif(!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- ===== Tournament Form ===== -->
        <form action="" method="POST" id="tournamentForm" class="form-container">

            <label>Tournament Title</label>
            <input type="text" name="title" required value="">

            <label>Description</label>
            <textarea name="description" rows="3"></textarea>

            <label>Tournament Start Date</label>
            <input type="date" name="start_date" required value="">

            <label>Tournament End Date</label>
            <input type="date" name="end_date" value="">

            <label>Max Teams</label>
            <input type="number" name="max_teams" min="2" required value="">
            
            <label>Registration Start Date</label>
            <input type="date" name="reg_start_date" required value="">

            <label>Registration Deadline</label>
            <input type="date" name="reg_deadline" required value="">

            <button type="submit" class="btn">Create Tournament</button>
        </form>

    </section>
</main>

<script>
const form = document.getElementById('tournamentForm');
const formMessage = document.getElementById('formMessage');

form.addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(form);
    formMessage.textContent = "Processing...";
    formMessage.style.color = "orange";

    fetch(window.location.href, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            formMessage.textContent = data.message;
            formMessage.style.color = "green";
            form.reset();
            setTimeout(()=>{ window.location.href = data.redirect_url; }, 1500);
        } else {
            formMessage.textContent = data.message;
            formMessage.style.color = "red";
        }
    })
    .catch(err=>{
        formMessage.textContent = "Unexpected error: "+err.message;
        formMessage.style.color="red";
    });
});
</script>
<script>
    // Wait until the page loads
    window.addEventListener('DOMContentLoaded', (event) => {
        // Clear the form fields
        document.getElementById('tournamentForm').reset();
        alert("<?= $success ?>"); // Optional: show success message
    });
</script>
</body>
</html>