<?php
session_start();
require_once "../backend/db.php";

// ========== ACCESS CONTROL ==========
if (!isset($_SESSION["account_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION["account_id"];
$role = $_SESSION["role"] ?? "";

if ($role !== "player") {
    echo "<p style='color:red; text-align:center;'>Access Denied.</p>";
    exit;
}

$message = "";
$message_type = "";

// ========== FETCH PLAYER TEAMS ==========
$stmt = $conn->prepare("
    SELECT DISTINCT t.team_id, t.team_name
    FROM teams t
    LEFT JOIN team_members tm ON t.team_id = tm.team_id
    WHERE t.created_by = :acc1 OR tm.account_id = :acc2
");
$stmt->execute([
    'acc1' => $account_id,
    'acc2' => $account_id
]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========== FETCH OPEN TOURNAMENTS (FULL FIELDS) ==========
$stmt2 = $conn->prepare("
    SELECT 
        tournament_id,
        title,
        description,
        start_date,
        end_date,
        reg_start_date,
        reg_end_date,
        status
    FROM tournaments
    WHERE status = 'open'
      AND reg_end_date >= NOW()
    ORDER BY reg_start_date ASC
");
$stmt2->execute();
$tournaments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ========== HANDLE REGISTRATION ==========
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $team_id = $_POST["team_id"] ?? null;
    $tournament_id = $_POST["tournament_id"] ?? null;

    if ($team_id && $tournament_id) {

        // Check if already registered
        $check = $conn->prepare("
            SELECT 1 
            FROM registrations
            WHERE tournament_id = :tid 
              AND team_id = :team
              AND account_id = :acc
        ");
        $check->execute([
            'tid' => $tournament_id,
            'team' => $team_id,
            'acc' => $account_id
        ]);

        if ($check->rowCount() > 0) {
            $message = "This team is already registered for that tournament.";
            $message_type = "error";
        } else {
            try {
                $insert = $conn->prepare("
                    INSERT INTO registrations (tournament_id, team_id, account_id, registered_at)
                    VALUES (:tid, :team, :acc, NOW())
                ");
                $insert->execute([
                    'tid' => $tournament_id,
                    'team' => $team_id,
                    'acc' => $account_id
                ]);

                $message = "Team successfully registered for the tournament!";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Registration failed: " . htmlspecialchars($e->getMessage());
                $message_type = "error";
            }
        }
    } else {
        $message = "Please select both a team and a tournament.";
        $message_type = "error";
    }

    // Fetch full tournament details after submit
    if ($tournament_id) {
        $stmt3 = $conn->prepare("
            SELECT 
                title, description, start_date, end_date,
                reg_start_date, reg_end_date, status
            FROM tournaments
            WHERE tournament_id = :tid
        ");
        $stmt3->execute(['tid' => $tournament_id]);
        $tournament = $stmt3->fetch(PDO::FETCH_ASSOC);

        // SAFE fallback formatting
        $reg_start = $tournament["reg_start_date"]
            ? date('F d, Y', strtotime($tournament['reg_start_date']))
            : "Not set";

        $reg_end   = $tournament["reg_end_date"]
            ? date('F d, Y', strtotime($tournament['reg_end_date']))
            : "Not set";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Team for Tournament</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="../assets/css/gaming_modern.css">
    <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body style="background: var(--bg-primary); min-height: 100vh;">
    <?php include '../includes/player/player_sidebar.php'; ?>

    <main class="org-main">
        <div class="gaming-hero" style="min-height: 250px;">
            <div class="gaming-hero__orb gaming-hero__orb--primary"></div>
            <div class="gaming-hero__orb gaming-hero__orb--secondary"></div>
            <div style="position: absolute; bottom: -100px; left: -100px; width: 350px; height: 350px; background: radial-gradient(circle, rgba(59,130,246,0.1), transparent 70%); filter: blur(50px); animation: float 12s ease-in-out infinite;"></div>

            <!-- Gradient Overlay -->
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, transparent 0%, rgba(15,15,15,0.5) 100%);"></div>

            <!-- Content -->
            <div style="position: relative; max-width: 1200px; margin: 0 auto;">
                <div style="display: inline-flex; align-items: center; gap: 0.75rem; background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,94,0,0.3); padding: 0.65rem 1.25rem; border-radius: 50px; margin-bottom: 1.25rem;">
                    <i class="fas fa-clipboard-list" style="color: #FF5E00; font-size: 1rem;"></i>
                    <span style="color: #fafafa; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Tournament Registration</span>
                </div>

                <h1 style="font-size: 3rem; font-weight: 900; margin: 0 0 0.75rem 0; letter-spacing: -1.5px; color: #fafafa;">
                    Register Your Team
                </h1>

                <p style="font-size: 1.1rem; color: #a1a1aa; max-width: 600px;">Choose a tournament and team to participate in competitive gaming</p>
            </div>
            </section>

            <style>
                @keyframes gridMove {
                    0% {
                        background-position: 0 0;
                    }

                    100% {
                        background-position: 50px 50px;
                    }
                }

                @keyframes float {

                    0%,
                    100% {
                        transform: translate(0, 0);
                    }

                    50% {
                        transform: translate(20px, -20px);
                    }
                }
            </style>

            <div style="padding: 0 2rem 2rem; max-width: 1200px; margin: 0 auto;">
                <?php if ($message): ?>
                    <div style="background: <?= $message_type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)' ?>; backdrop-filter: blur(20px); border: 1px solid <?= $message_type === 'success' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' ?>; color: white; padding: 1.25rem 1.75rem; border-radius: 14px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 8px 24px <?= $message_type === 'success' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' ?>;">
                        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>" style="font-size: 1.5rem;"></i>
                        <span style="font-weight: 600; font-size: 1rem;"><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Registration Form Card -->
                <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; margin-bottom: 2rem;">
                    <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,94,0,0.1), transparent 70%); filter: blur(60px);"></div>
                    <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                    <div style="position: relative; background: linear-gradient(135deg, rgba(255,94,0,0.15) 0%, transparent 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #FF5E00, #FF7B33); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(255,94,0,0.4);">
                                <i class="fas fa-clipboard-list" style="color: white; font-size: 1.3rem;"></i>
                            </div>
                            <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Registration Form</h3>
                        </div>
                    </div>

                    <div style="position: relative; padding: 2rem;">
                        <form method="POST" style="display: grid; gap: 1.5rem;">
                            <!-- Tournament Select -->
                            <div>
                                <label style="display: flex; align-items: center; gap: 0.5rem; color: #fafafa; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">
                                    <i class="fas fa-trophy" style="color: #FF5E00;"></i>
                                    Select Tournament
                                </label>
                                <select name="tournament_id" id="tournament_id" required style="width: 100%; background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; color: #fafafa; font-size: 1rem; transition: all 0.3s;" onfocus="this.style.borderColor='rgba(255,94,0,0.5)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                                    <option value="" style="background: #18181b;">-- Choose a Tournament --</option>
                                    <?php foreach ($tournaments as $t): ?>
                                        <option value="<?= $t['tournament_id'] ?>" style="background: #18181b;">
                                            <?= htmlspecialchars($t['title']) ?>
                                            <?php if (!empty($t['reg_end_date'])): ?>
                                                (Deadline: <?= date('M d, Y', strtotime($t['reg_end_date'])) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Team Select -->
                            <div>
                                <label style="display: flex; align-items: center; gap: 0.5rem; color: #fafafa; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">
                                    <i class="fas fa-users" style="color: #8b5cf6;"></i>
                                    Select Your Team
                                </label>
                                <select name="team_id" id="team_id" required style="width: 100%; background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; color: #fafafa; font-size: 1rem; transition: all 0.3s;" onfocus="this.style.borderColor='rgba(139,92,246,0.5)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                                    <option value="" style="background: #18181b;">-- Choose a Team --</option>
                                    <?php foreach ($teams as $team): ?>
                                        <option value="<?= $team['team_id'] ?>" style="background: #18181b;"><?= htmlspecialchars($team['team_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button type="submit" style="flex: 1; background: linear-gradient(135deg, #FF5E00, #FF7B33); color: white; border: none; border-radius: 12px; padding: 1.1rem; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 8px 24px rgba(255,94,0,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 32px rgba(255,94,0,0.5)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 24px rgba(255,94,0,0.3)'">
                                    <i class="fas fa-check-circle"></i> Register Team
                                </button>
                                <a href="player_dashboard.php" style="flex: 0.4; background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); color: #fafafa; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1.1rem; font-weight: 600; font-size: 1rem; text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.75rem;" onmouseover="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.background='rgba(39,39,42,0.6)'">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Available Tournaments -->
                <?php if (count($tournaments) > 0): ?>
                    <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden;">
                        <div style="position: absolute; top: -100px; left: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(59,130,246,0.1), transparent 70%); filter: blur(60px);"></div>
                        <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                        <div style="position: relative; background: linear-gradient(135deg, rgba(59,130,246,0.15) 0%, transparent 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(59,130,246,0.4);">
                                    <i class="fas fa-trophy" style="color: white; font-size: 1.3rem;"></i>
                                </div>
                                <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Available Tournaments</h3>
                            </div>
                        </div>

                        <div style="position: relative; padding: 2rem; display: grid; gap: 1.25rem;">
                            <?php foreach ($tournaments as $t): ?>
                                <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.5rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(59,130,246,0.3)'; this.style.background='rgba(39,39,42,0.8)'; this.style.transform='translateX(6px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'; this.style.transform=''">
                                    <div style="display: flex; align-items: start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
                                        <div style="flex: 1;">
                                            <h4 style="color: #fafafa; font-size: 1.2rem; font-weight: 700; margin: 0 0 0.5rem 0; letter-spacing: -0.3px;"><?= htmlspecialchars($t['title']) ?></h4>
                                            <p style="color: #a1a1aa; font-size: 0.95rem; margin: 0; line-height: 1.6;"><?= htmlspecialchars($t['description']) ?></p>
                                        </div>
                                        <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.4rem 0.85rem; border-radius: 10px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;"><?= ucfirst($t['status']) ?></span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 28px; height: 28px; background: rgba(59,130,246,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-calendar-alt" style="color: #3b82f6; font-size: 0.8rem;"></i>
                                        </div>
                                        <span style="color: #71717a; font-size: 0.9rem; font-weight: 500;">Start: <?= date('M d, Y', strtotime($t['start_date'])) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
    </main>
</body>

</html>