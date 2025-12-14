<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../backend/db.php";

if (!isset($_SESSION["account_id"]) || $_SESSION["role"] !== "player") {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION["account_id"];

$teams_query = "
    SELECT DISTINCT
        t.team_id,
        t.team_name,
        t.game_name,
        t.team_logo,
        t.introduction,
        t.max_members,
        t.created_at,
        a.username AS creator_name,
        (SELECT COUNT(*) FROM team_members WHERE team_id = t.team_id) AS member_count,
        CASE WHEN t.created_by = :account_id THEN 1 ELSE 0 END AS is_leader,
        tm.role AS member_role
    FROM teams t
    LEFT JOIN accounts a ON t.created_by = a.account_id
    INNER JOIN team_members tm ON t.team_id = tm.team_id
    WHERE tm.account_id = :account_id2
    ORDER BY is_leader DESC, t.created_at DESC
";

try {
    $teams_stmt = $conn->prepare($teams_query);
    $teams_stmt->execute(['account_id' => $account_id, 'account_id2' => $account_id]);
    $my_teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Teams - GameX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="../assets/css/gaming_modern.css">
    <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body style="background: var(--bg-primary); min-height: 100vh;">
    <?php include '../includes/player/player_sidebar.php'; ?>

    <main class="org-main">
        <!-- Gaming Hero Section -->
        <div class="gaming-hero">
            <div class="gaming-hero__orb gaming-hero__orb--secondary"></div>
            <div class="gaming-hero__orb gaming-hero__orb--primary"></div>

            <!-- Content -->
            <div style="position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
                <div style="flex: 1; min-width: 300px;">
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.25rem; background: rgba(139,92,246,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(139,92,246,0.3); border-radius: 50px; margin-bottom: 1rem;">
                        <div style="width: 8px; height: 8px; background: #8b5cf6; border-radius: 50%; animation: pulse 2s ease-in-out infinite;"></div>
                        <span style="color: #8b5cf6; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Your Collection</span>
                    </div>
                    <h1 style="font-size: 2.5rem; font-weight: 800; margin: 0 0 1rem 0; background: linear-gradient(135deg, #ffffff 0%, #a1a1aa 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1.2;">My Gaming<br />Teams</h1>
                    <p style="color: #a1a1aa; font-size: 1rem; line-height: 1.6; max-width: 500px; margin: 0;">Manage your team memberships, track your roles, and coordinate with teammates for upcoming competitions.</p>

                    <!-- Stats -->
                    <div style="display: flex; gap: 2rem; margin-top: 1.5rem;">
                        <div>
                            <div style="font-size: 2rem; font-weight: 800; color: #8b5cf6;"><?= count($my_teams) ?></div>
                            <div style="font-size: 0.85rem; color: #71717a; text-transform: uppercase; letter-spacing: 0.5px;">Your Teams</div>
                        </div>
                        <div>
                            <div style="font-size: 2rem; font-weight: 800; color: #FF5E00;"><?= count(array_filter($my_teams, fn($t) => $t['is_leader'])) ?></div>
                            <div style="font-size: 0.85rem; color: #71717a; text-transform: uppercase; letter-spacing: 0.5px;">Leading</div>
                        </div>
                    </div>
                </div>

                <!-- Action -->
                <div>
                    <a href="teams.php" style="padding: 1rem 2rem; background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fafafa; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateX(4px)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.transform=''">
                        <i class="fas fa-compass" style="font-size: 1.2rem;"></i>
                        <div style="text-align: left;">
                            <div style="font-size: 0.75rem; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.5px;">Discover</div>
                            <div>Browse All Teams</div>
                        </div>
                        <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 0.9rem;"></i>
                    </a>
                </div>
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
                        transform: translate(0, 0) rotate(0deg);
                    }

                    33% {
                        transform: translate(30px, -30px) rotate(5deg);
                    }

                    66% {
                        transform: translate(-20px, 20px) rotate(-5deg);
                    }
                }

                @keyframes pulse {

                    0%,
                    100% {
                        opacity: 1;
                        transform: scale(1);
                    }

                    50% {
                        opacity: 0.5;
                        transform: scale(1.5);
                    }
                }
            </style>

            <!-- Teams Content -->
            <div style="padding: 2rem;">
                <?php if (count($my_teams) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($my_teams as $team): ?>
                            <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onmouseover="this.style.borderColor='rgba(139,92,246,0.5)'; this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.4), 0 0 0 1px rgba(139,92,246,0.2)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.transform=''; this.style.boxShadow=''">
                                <!-- Top Accent Line -->
                                <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, #8b5cf6, transparent); opacity: 0; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'"></div>

                                <!-- Background Pattern -->
                                <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                                <!-- Header -->
                                <div style="position: relative; padding: 1.75rem; background: linear-gradient(135deg, rgba(139,92,246,0.05) 0%, transparent 100%);">
                                    <div style="display: flex; align-items: start; gap: 1.25rem;">
                                        <!-- Logo with Glow -->
                                        <?php if ($team['team_logo']): ?>
                                            <div style="position: relative; flex-shrink: 0;">
                                                <div style="width: 70px; height: 70px; border-radius: 12px; overflow: hidden; border: 2px solid rgba(139,92,246,0.3); box-shadow: 0 8px 24px rgba(0,0,0,0.3); position: relative; z-index: 1;">
                                                    <img src="../<?= htmlspecialchars($team['team_logo']) ?>" alt="<?= htmlspecialchars($team['team_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <div style="position: absolute; inset: -10px; background: radial-gradient(circle, rgba(139,92,246,0.2), transparent 70%); filter: blur(15px); z-index: 0;"></div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Team Info -->
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="display: flex; align-items: start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem;">
                                                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #fafafa; line-height: 1.3; letter-spacing: -0.5px;">
                                                    <?= htmlspecialchars($team['team_name']) ?>
                                                </h3>
                                                <?php if ($team['is_leader']): ?>
                                                    <span style="flex-shrink: 0; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 12px rgba(16,185,129,0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                                                        <i class="fas fa-crown" style="font-size: 0.7rem;"></i> Leader
                                                    </span>
                                                <?php else: ?>
                                                    <span style="flex-shrink: 0; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 12px rgba(59,130,246,0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                                                        <i class="fas fa-user" style="font-size: 0.7rem;"></i> <?= ucfirst($team['member_role']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Game Tag -->
                                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.9rem; background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.2); border-radius: 6px;">
                                                <i class="fas fa-gamepad" style="color: #8b5cf6; font-size: 0.8rem;"></i>
                                                <span style="color: #8b5cf6; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($team['game_name']) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($team['introduction']): ?>
                                        <p style="color: #71717a; font-size: 0.85rem; line-height: 1.5; margin: 1rem 0 0 0; padding: 0.75rem; background: rgba(39,39,42,0.3); border-radius: 8px; border-left: 3px solid #8b5cf6;">
                                            <?= htmlspecialchars(substr($team['introduction'], 0, 90)) ?><?= strlen($team['introduction']) > 90 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Stats Section -->
                                <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.25rem;">
                                        <!-- Members Stat -->
                                        <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(139,92,246,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(139,92,246,0.3);">
                                                    <i class="fas fa-users" style="color: white; font-size: 0.9rem;"></i>
                                                </div>
                                                <div style="flex: 1;">
                                                    <div style="font-size: 0.7rem; color: #71717a; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.2rem;">Members</div>
                                                    <div style="font-size: 1.1rem; color: #fafafa; font-weight: 700;"><?= $team['member_count'] ?><span style="color: #71717a; font-size: 0.9rem; font-weight: 500;"> / <?= $team['max_members'] ?></span></div>
                                                </div>
                                            </div>
                                            <!-- Progress Bar -->
                                            <div style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                                                <div style="height: 100%; background: linear-gradient(90deg, #8b5cf6, #7c3aed); width: <?= ($team['member_count'] / $team['max_members']) * 100 ?>%; transition: width 0.3s; box-shadow: 0 0 10px rgba(139,92,246,0.5);"></div>
                                            </div>
                                        </div>

                                        <!-- Join Date -->
                                        <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(255,94,0,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #FF5E00, #FF7B33); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255,94,0,0.3);">
                                                    <i class="fas fa-calendar-check" style="color: white; font-size: 0.9rem;"></i>
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="font-size: 0.7rem; color: #71717a; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.2rem;">Joined</div>
                                                    <div style="font-size: 0.9rem; color: #fafafa; font-weight: 600;"><?= date('M d, Y', strtotime($team['created_at'])) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div style="display: flex; gap: 0.75rem;">
                                        <a href="view_team.php?team_id=<?= $team['team_id'] ?>" style="flex: 1; position: relative; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: all 0.3s; overflow: hidden; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 16px rgba(139,92,246,0.3); border: 1px solid rgba(255,255,255,0.1);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(139,92,246,0.5)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 16px rgba(139,92,246,0.3)'">
                                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent); pointer-events: none;"></div>
                                            <i class="fas fa-eye" style="position: relative;"></i>
                                            <span style="position: relative;">View</span>
                                        </a>
                                        <?php if ($team['is_leader']): ?>
                                            <a href="manage_team.php?team_id=<?= $team['team_id'] ?>" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem; background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fafafa; text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: all 0.3s; text-transform: uppercase; letter-spacing: 0.5px;" onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='#8b5cf6'" onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(255,255,255,0.1)'">
                                                <i class="fas fa-cog"></i>
                                                <span>Manage</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="org-empty-state">
                        <div class="org-empty-icon">
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <h3>No Teams Yet</h3>
                        <p>You haven't created or joined any teams. Start by browsing available teams or create your own!</p>
                        <a href="teams.php" class="org-btn org-btn-primary">
                            <i class="fas fa-search"></i> Browse Available Teams
                        </a>
                    </div>
                <?php endif; ?>
            </div>
    </main>
</body>

</html>