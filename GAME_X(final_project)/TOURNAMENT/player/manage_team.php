<?php
session_start();
require_once __DIR__ . '/../backend/db.php';

// ================== AUTH CHECK ==================
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'player') {
    die("Unauthorized access.");
}

$account_id = $_SESSION['account_id'];

// ================== GET TEAM ID ==================
if (!isset($_GET['team_id'])) {
    die("Team ID missing.");
}

$team_id = (int) $_GET['team_id'];

// ================== VERIFY TEAM OWNER ==================
// ✅ FIXED COLUMN NAME HERE
$stmtTeam = $conn->prepare("
    SELECT *
    FROM teams
    WHERE team_id = ? AND created_by = ?
");
$stmtTeam->execute([$team_id, $account_id]);
$team = $stmtTeam->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    die("You do not have permission to manage this team.");
}

// ================== GET TEAM MEMBERS ==================
$stmtMembers = $conn->prepare("
    SELECT u.username, tm.joined_at
    FROM team_members tm
    JOIN accounts u ON tm.account_id = u.account_id
    WHERE tm.team_id = ?
");
$stmtMembers->execute([$team_id]);
$members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team - GameX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="../assets/css/gaming_modern.css">
    <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body>
    <?php include '../includes/player/player_sidebar.php'; ?>

    <main class="org-main">
        <!-- Gaming Hero Section -->
        <section class="gaming-hero">
            <div class="gaming-hero__bg"></div>
            <div class="gaming-hero__content">
                <div class="gaming-hero__badge">
                    <i class="fas fa-cog"></i>
                    <span>Manage Team</span>
                </div>
                <h1 class="gaming-hero__title"><?= htmlspecialchars($team['team_name']) ?></h1>
                <p class="gaming-hero__subtitle">Manage your team members, settings, and team performance</p>
                <div style="margin-top: 1.5rem;">
                    <a href="view_team.php?team_id=<?= $team_id ?>" class="btn-gaming btn-gaming--secondary">
                        <i class="fas fa-arrow-left"></i> Back to Team
                    </a>
                </div>
            </div>
        </section>

        <div class="content-section">
            <!-- Team Members Card with Complex Design -->
            <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(255,94,0,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'">
                <!-- Background Effects -->
                <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,94,0,0.1), transparent 70%); filter: blur(60px);"></div>
                <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                <!-- Header -->
                <div style="position: relative; background: linear-gradient(135deg, rgba(255,94,0,0.15) 0%, transparent 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #FF5E00, #FF7B33); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(255,94,0,0.4);">
                                <i class="fas fa-users" style="color: white; font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Team Members</h3>
                                <p style="margin: 0; color: #71717a; font-size: 0.9rem; font-weight: 500;">Manage your team roster</p>
                            </div>
                        </div>
                        <div style="background: rgba(255,94,0,0.2); border: 1px solid rgba(255,94,0,0.3); padding: 0.65rem 1.25rem; border-radius: 50px; font-size: 0.95rem; font-weight: 700; color: #FF7B33;">
                            <?= count($members) ?> Members
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div style="position: relative; padding: 2rem;">
                    <?php if (count($members) === 0): ?>
                        <!-- Empty State -->
                        <div style="background: rgba(39,39,42,0.4); border: 2px dashed rgba(113,113,122,0.3); border-radius: 16px; padding: 4rem 2rem; text-align: center;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: rgba(255,94,0,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-users-slash" style="font-size: 2.5rem; color: #FF5E00; opacity: 0.6;"></i>
                            </div>
                            <h3 style="color: #fafafa; font-size: 1.5rem; font-weight: 700; margin: 0 0 0.75rem 0;">No Members Yet</h3>
                            <p style="color: #71717a; font-size: 1rem; margin: 0;">Start inviting players to join your team and build your roster</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 1.25rem;">
                            <?php foreach ($members as $member): ?>
                                <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; transition: all 0.3s; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateX(8px)'; this.style.borderColor='rgba(255,94,0,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                    <!-- Glow Effect -->
                                    <div style="position: absolute; top: 0; left: 0; width: 100px; height: 100%; background: linear-gradient(90deg, rgba(255,94,0,0.08), transparent); opacity: 0; transition: opacity 0.3s;"></div>

                                    <!-- Avatar -->
                                    <div style="position: relative; width: 60px; height: 60px; border-radius: 14px; background: linear-gradient(135deg, #FF5E00, #FF7B33); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.3rem; flex-shrink: 0; box-shadow: 0 8px 24px rgba(255,94,0,0.4); border: 2px solid rgba(255,255,255,0.1);">
                                        <?= strtoupper(substr($member['username'], 0, 2)) ?>
                                        <!-- Pulse Ring -->
                                        <div style="position: absolute; inset: -3px; border-radius: 14px; border: 2px solid rgba(255,94,0,0.3); animation: pulse 2s ease-in-out infinite;"></div>
                                    </div>

                                    <!-- Member Info -->
                                    <div style="flex: 1; position: relative;">
                                        <h4 style="margin: 0 0 0.5rem 0; font-size: 1.15rem; font-weight: 700; color: #fafafa; letter-spacing: -0.3px;"><?= htmlspecialchars($member['username']) ?></h4>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 24px; height: 24px; background: rgba(16,185,129,0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar" style="color: #10b981; font-size: 0.75rem;"></i>
                                            </div>
                                            <p style="margin: 0; font-size: 0.9rem; color: #a1a1aa; font-weight: 500;">
                                                Joined <?= date('M d, Y', strtotime($member['joined_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.5;
                    transform: scale(1.05);
                }
            }
        </style>
    </main>
</body>

</html>