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
<html>
<head>
    <title>Create Tournament</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* ====================== SIDEBAR OFFSET + TOPBAR ====================== */
        .topbar {
            position: fixed;
            top: 0;
            left: 260px;
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

        /* ====================== MAIN CONTENT AREA ====================== */
        main.content-area {
            margin-left: 280px;
            padding: 100px 60px 40px;
            min-height: 100vh;
            background: #f4f5f7;
            display: flex;
            justify-content: center;
        }

        /* ====================== FORM CARD ====================== */
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

        /* ====================== MESSAGES ====================== */
        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
        }
        .message.success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        /* ====================== FORM STYLING ====================== */
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
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
            box-sizing: border-box;
        }

        input:focus, textarea:focus {
            border-color: #ff6600;
            outline: none;
            box-shadow: 0 0 5px rgba(255, 102, 0, 0.3);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* ====================== BUTTON ====================== */
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
            margin-top: 10px;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #e55a00;
        }

        .btn:active {
            transform: scale(0.98);
        }

        /* ====================== RESPONSIVE DESIGN ====================== */
        @media (max-width: 768px) {
            .topbar {
                left: 0;
                padding: 0 20px;
            }

            main.content-area {
                margin-left: 0;
                padding: 90px 20px 40px;
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
    <main class="content-area">
        <section class="form-section">

            <!-- ===== Feedback Messages ===== -->
            <?php if($success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php elseif(!empty($error)): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- ===== Tournament Form ===== -->
            <form action="" method="POST" class="form-container">

                <label for="title">Tournament Title *</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($title ?? '') ?>">

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>

                <label for="start_date">Tournament Start Date *</label>
                <input type="date" id="start_date" name="start_date" required value="<?= htmlspecialchars($start_date ?? '') ?>">

                <label for="end_date">Tournament End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>">

                <label for="max_teams">Max Teams *</label>
                <input type="number" id="max_teams" name="max_teams" min="2" required value="<?= htmlspecialchars($max_teams ?? '') ?>">
                
                <label for="game">Game *</label>
                <input type="text" id="game" name="game" required value="<?= htmlspecialchars($game ?? '') ?>" placeholder="e.g., Mobile Legends, Call of Duty">
                
                <label for="reg_start_date">Registration Start Date *</label>
                <input type="date" id="reg_start_date" name="reg_start_date" required value="<?= htmlspecialchars($reg_start_date ?? '') ?>">

                <label for="reg_end_date">Registration Deadline *</label>
                <input type="date" id="reg_end_date" name="reg_end_date" required value="<?= htmlspecialchars($reg_end_date ?? '') ?>">

                <button type="submit" class="btn">Create Tournament</button>
            </form>

        </section>
    </main>

</body>
</html>