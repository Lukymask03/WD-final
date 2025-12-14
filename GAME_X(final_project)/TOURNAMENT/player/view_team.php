<?php
session_start();
require_once "../backend/db.php";

// Security check
if (!isset($_SESSION["account_id"]) || $_SESSION["role"] !== "player") {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION["account_id"];
$team_id = $_GET['team_id'] ?? 0;

if (!$team_id) {
    header("Location: teams.php");
    exit;
}

// Fetch team details
$team_query = "
    SELECT t.*, a.username AS creator_name,
           (SELECT COUNT(*) FROM team_members WHERE team_id = t.team_id) AS member_count,
           CASE WHEN t.created_by = :account_id THEN 1 ELSE 0 END AS is_leader
    FROM teams t
    LEFT JOIN accounts a ON t.created_by = a.account_id
    WHERE t.team_id = :team_id
";

$team_stmt = $conn->prepare($team_query);
$team_stmt->execute(['account_id' => $account_id, 'team_id' => $team_id]);
$team = $team_stmt->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    $_SESSION['team_error'] = "Team not found.";
    header("Location: teams.php");
    exit;
}

// Fetch team members
$members_query = "
    SELECT tm.role, tm.joined_at, a.account_id, a.username,
           pp.gamer_tag, pp.fullname
    FROM team_members tm
    INNER JOIN accounts a ON tm.account_id = a.account_id
    LEFT JOIN player_profiles pp ON a.account_id = pp.account_id
    WHERE tm.team_id = :team_id
    ORDER BY FIELD(tm.role, 'leader', 'member'), tm.joined_at ASC
";

$members_stmt = $conn->prepare($members_query);
$members_stmt->execute(['team_id' => $team_id]);
$members = $members_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if current user is a member
$is_member = false;
foreach ($members as $member) {
    if ($member['account_id'] == $account_id) {
        $is_member = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($team['team_name']) ?> - GameX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="../assets/css/gaming_modern.css">
    <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body>
    <?php include '../includes/player/player_sidebar.php'; ?>

    <main class="org-main">
        <!-- Hero Banner with Team Logo -->
        <?php
        // Map game names to hero images
        $game_name_lower = strtolower($team['game_name']);
        $hero_image = '../assets/images/heroes/';
        if (strpos($game_name_lower, 'valorant') !== false) {
            $hero_image .= 'valorant.jpg';
        } elseif (strpos($game_name_lower, 'dota') !== false || strpos($game_name_lower, 'dota 2') !== false) {
            $hero_image .= 'dota2.webp';
        } elseif (strpos($game_name_lower, 'league of legends') !== false || strpos($game_name_lower, 'lol') !== false) {
            $hero_image .= 'lol.jpg';
        } elseif (strpos($game_name_lower, 'mobile legends') !== false || strpos($game_name_lower, 'ml') !== false) {
            $hero_image .= 'ml.jpg';
        } elseif (strpos($game_name_lower, 'call of duty') !== false || strpos($game_name_lower, 'cod') !== false) {
            $hero_image .= 'codm.jpg';
        } else {
            $hero_image = '../assets/images/ESPORTS_3.png'; // Default fallback
        }
        ?>
        <div style="position: relative; height: 360px; background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0f0f0f 100%); overflow: hidden; margin-bottom: 2rem; border-radius: 24px;">
            <!-- Game Hero Background with Parallax Effect -->
            <div style="position: absolute; inset: 0; background: url('<?= $hero_image ?>') center/cover no-repeat; opacity: 0.2; transform: scale(1.1); filter: blur(2px);"></div>

            <!-- Animated Grid Pattern -->
            <div style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,94,0,0.05) 2px, transparent 2px), linear-gradient(90deg, rgba(255,94,0,0.05) 2px, transparent 2px); background-size: 40px 40px; animation: gridMove 20s linear infinite;"></div>

            <!-- Gradient Orbs -->
            <div style="position: absolute; top: -30%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(255,94,0,0.2), transparent 70%); border-radius: 50%; filter: blur(60px); animation: float 15s ease-in-out infinite;"></div>
            <div style="position: absolute; bottom: -30%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(139,92,246,0.15), transparent 70%); border-radius: 50%; filter: blur(50px); animation: float 12s ease-in-out infinite reverse;"></div>

            <!-- Gradient Overlay -->
            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,94,0,0.85) 0%, rgba(255,123,51,0.7) 50%, rgba(139,92,246,0.8) 100%);"></div>

            <!-- Top Accent Line -->
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, transparent, #FF5E00, #8b5cf6, transparent);"></div>

            <!-- Content -->
            <div style="position: relative; z-index: 1; height: 100%; display: flex; align-items: center; padding: 0 2.5rem; gap: 2.5rem;">
                <!-- Team Logo with Complex Effects -->
                <div style="position: relative; flex-shrink: 0;">
                    <?php if ($team['team_logo']): ?>
                        <!-- Glow Effect Behind Logo -->
                        <div style="position: absolute; inset: -20px; background: radial-gradient(circle, rgba(255,255,255,0.3), transparent 70%); filter: blur(30px); animation: pulse 3s ease-in-out infinite;"></div>
                        <!-- Logo Container -->
                        <div style="position: relative; width: 180px; height: 180px; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 24px; padding: 1.25rem; box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; border: 3px solid rgba(255,255,255,0.2);">
                            <img src="../<?= htmlspecialchars($team['team_logo']) ?>" alt="<?= htmlspecialchars($team['team_name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.2));">
                        </div>
                        <!-- Rotating Ring -->
                        <div style="position: absolute; inset: -10px; border: 2px solid rgba(255,255,255,0.2); border-radius: 26px; border-top-color: rgba(255,94,0,0.6); animation: rotate 3s linear infinite;"></div>
                    <?php else: ?>
                        <div style="position: relative; width: 180px; height: 180px; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 20px 60px rgba(0,0,0,0.4); border: 3px solid rgba(255,255,255,0.2);">
                            <i class="fas fa-shield-alt" style="font-size: 5rem; color: #FF5E00; opacity: 0.9;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Team Info with Enhanced Typography -->
                <div style="flex: 1; min-width: 0; color: white;">
                    <!-- Game Badge -->
                    <div style="display: inline-flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.15); backdrop-filter: blur(20px); padding: 0.6rem 1.5rem; border-radius: 50px; margin-bottom: 1.25rem; font-size: 0.9rem; font-weight: 600; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
                        <div style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981; animation: pulse 2s ease-in-out infinite;"></div>
                        <i class="fas fa-gamepad" style="font-size: 1.1rem;"></i>
                        <?= htmlspecialchars($team['game_name']) ?>
                    </div>
                    <!-- Team Name -->
                    <h1 style="margin: 0 0 1rem 0; font-size: 3rem; font-weight: 900; text-shadow: 0 4px 12px rgba(0,0,0,0.4), 0 2px 4px rgba(0,0,0,0.3); letter-spacing: -1px; line-height: 1.1;">
                        <?= htmlspecialchars($team['team_name']) ?>
                    </h1>
                    <!-- Stats Row -->
                    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 1.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; background: rgba(0,0,0,0.3); backdrop-filter: blur(10px); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-users" style="font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Members</div>
                                <div style="font-size: 1.3rem; font-weight: 800;"><?= $team['member_count'] ?><span style="opacity: 0.6; font-size: 1rem;"> / <?= $team['max_members'] ?></span></div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; background: rgba(0,0,0,0.3); backdrop-filter: blur(10px); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-shield" style="font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Leader</div>
                                <div style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($team['creator_name']) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons with Enhanced Design -->
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="teams.php" style="padding: 1rem 1.75rem; background: rgba(255,255,255,0.1); backdrop-filter: blur(20px); color: white; border-radius: 12px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem;" onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateX(-4px)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform=''">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <?php if ($team['is_leader']): ?>
                            <button onclick="openInviteModal()" style="padding: 1rem 1.75rem; background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85)); color: #FF5E00; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.75rem; transition: all 0.3s; box-shadow: 0 8px 24px rgba(255,255,255,0.2); text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 32px rgba(255,255,255,0.3)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 24px rgba(255,255,255,0.2)'">
                                <i class="fas fa-user-plus"></i> Invite
                            </button>
                            <a href="manage_team.php?team_id=<?= $team_id ?>" style="padding: 1rem 1.75rem; background: linear-gradient(135deg, #FF5E00, #FF7B33); color: white; border-radius: 12px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.75rem; box-shadow: 0 8px 24px rgba(255,94,0,0.4); transition: all 0.3s; border: 1px solid rgba(255,255,255,0.1); text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 32px rgba(255,94,0,0.5)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 24px rgba(255,94,0,0.4)'">
                                <i class="fas fa-cog"></i> Manage
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes gridMove {
                0% {
                    background-position: 0 0;
                }

                100% {
                    background-position: 40px 40px;
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

            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.7;
                    transform: scale(1.05);
                }
            }

            @keyframes rotate {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }
        </style>

        <!-- Content -->
        <div style="padding: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
                <!-- Team Info Card with Complex Design -->
                <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(255,94,0,0.3)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 20px 60px rgba(255,94,0,0.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.transform=''; this.style.boxShadow=''">
                    <!-- Background Effects -->
                    <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,94,0,0.12), transparent 70%); filter: blur(60px); animation: float 15s ease-in-out infinite;"></div>
                    <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                    <!-- Card Header with Gradient -->
                    <div style="position: relative; background: linear-gradient(135deg, rgba(255,94,0,0.15) 0%, transparent 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 4px; height: 32px; background: linear-gradient(to bottom, #FF5E00, #FF7B33); border-radius: 2px;"></div>
                            <h3 style="color: #fafafa; font-size: 1.4rem; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Team Information</h3>
                        </div>
                        <?php if ($team['introduction']): ?>
                            <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); padding: 1.25rem; border-radius: 12px; border-left: 3px solid #FF5E00; position: relative; overflow: hidden;">
                                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: radial-gradient(circle, rgba(255,94,0,0.08), transparent); filter: blur(20px);"></div>
                                <p style="position: relative; margin: 0; color: #d4d4d8; line-height: 1.7; font-size: 0.95rem;"><?= nl2br(htmlspecialchars($team['introduction'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Body -->
                    <div style="position: relative; padding: 2rem;">
                        <!-- Stats Grid with Glassmorphism -->
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
                            <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.25rem; text-align: center; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(255,94,0,0.4)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                <div style="width: 48px; height: 48px; margin: 0 auto 0.75rem; background: linear-gradient(135deg, #FF5E00, #FF7B33); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(255,94,0,0.3);">
                                    <i class="fas fa-gamepad" style="color: white; font-size: 1.3rem;"></i>
                                </div>
                                <div style="color: #71717a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Game</div>
                                <div style="color: #fafafa; font-weight: 800; font-size: 1.1rem;"><?= htmlspecialchars($team['game_name']) ?></div>
                            </div>
                            <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.25rem; text-align: center; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(6,182,212,0.4)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                <div style="width: 48px; height: 48px; margin: 0 auto 0.75rem; background: linear-gradient(135deg, #06b6d4, #0891b2); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(6,182,212,0.3);">
                                    <i class="fas fa-users" style="color: white; font-size: 1.3rem;"></i>
                                </div>
                                <div style="color: #71717a; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Members</div>
                                <div style="color: #fafafa; font-weight: 800; font-size: 1.1rem;"><?= $team['member_count'] ?> <span style="color: #71717a; font-weight: 500;">/ <?= $team['max_members'] ?></span></div>
                            </div>
                        </div>

                        <!-- Additional Info with Complex Layout -->
                        <div style="display: grid; gap: 1rem;">
                            <div style="background: rgba(139,92,246,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(139,92,246,0.2); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(139,92,246,0.4)'; this.style.background='rgba(139,92,246,0.15)'" onmouseout="this.style.borderColor='rgba(139,92,246,0.2)'; this.style.background='rgba(139,92,246,0.1)'">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 16px rgba(139,92,246,0.4);">
                                    <i class="fas fa-user-shield" style="color: white; font-size: 1.1rem;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="color: #a1a1aa; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Team Leader</div>
                                    <div style="color: #fafafa; font-weight: 700; font-size: 1.05rem;"><?= htmlspecialchars($team['creator_name']) ?></div>
                                </div>
                            </div>
                            <div style="background: rgba(16,185,129,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(16,185,129,0.2); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(16,185,129,0.4)'; this.style.background='rgba(16,185,129,0.15)'" onmouseout="this.style.borderColor='rgba(16,185,129,0.2)'; this.style.background='rgba(16,185,129,0.1)'">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 16px rgba(16,185,129,0.4);">
                                    <i class="fas fa-calendar-alt" style="color: white; font-size: 1.1rem;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="color: #a1a1aa; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Established</div>
                                    <div style="color: #fafafa; font-weight: 700; font-size: 1.05rem;"><?= date('F j, Y', strtotime($team['created_at'])) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members Card with Complex Design -->
                <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(139,92,246,0.3)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 20px 60px rgba(139,92,246,0.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.transform=''; this.style.boxShadow=''">
                    <!-- Background Effects -->
                    <div style="position: absolute; top: -100px; left: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(139,92,246,0.12), transparent 70%); filter: blur(60px); animation: float 12s ease-in-out infinite;"></div>
                    <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                    <!-- Card Header -->
                    <div style="position: relative; background: linear-gradient(135deg, rgba(139,92,246,0.15) 0%, transparent 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 4px; height: 32px; background: linear-gradient(to bottom, #8b5cf6, #7c3aed); border-radius: 2px;"></div>
                                <h3 style="color: #fafafa; font-size: 1.4rem; font-weight: 800; margin: 0; letter-spacing: -0.5px; display: flex; align-items: center; gap: 0.75rem;">
                                    <i class="fas fa-users"></i> Team Roster
                                </h3>
                            </div>
                            <span style="background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.3); padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 700; color: #c4b5fd;"><?= count($members) ?> Members</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div style="position: relative; padding: 1.5rem;">
                        <div style="display: grid; gap: 1rem;">
                            <?php foreach ($members as $member): ?>
                                <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.3s; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateX(6px)'; this.style.borderColor='rgba(255,94,0,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.transform=''; this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                    <!-- Glow Effect -->
                                    <div style="position: absolute; top: 0; left: 0; width: 100px; height: 100%; background: linear-gradient(90deg, rgba(255,94,0,0.1), transparent); opacity: 0; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'"></div>

                                    <!-- Avatar -->
                                    <div style="position: relative; width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #FF5E00, #FF7B33); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 8px 24px rgba(255,94,0,0.4); border: 2px solid rgba(255,255,255,0.1);">
                                        <?= strtoupper(substr($member['username'], 0, 2)) ?>
                                        <!-- Pulse Ring -->
                                        <div style="position: absolute; inset: -3px; border-radius: 14px; border: 2px solid rgba(255,94,0,0.3); animation: pulse 2s ease-in-out infinite;"></div>
                                    </div>

                                    <!-- Member Info -->
                                    <div style="flex: 1; min-width: 0; position: relative;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem; flex-wrap: wrap;">
                                            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #fafafa; letter-spacing: -0.25px;"><?= htmlspecialchars($member['username']) ?></h4>
                                            <?php if ($member['role'] === 'leader'): ?>
                                                <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.3rem 0.75rem; border-radius: 8px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 12px rgba(16,185,129,0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <i class="fas fa-crown"></i> Leader
                                                </span>
                                            <?php else: ?>
                                                <span style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 0.3rem 0.75rem; border-radius: 8px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 12px rgba(59,130,246,0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <i class="fas fa-user"></i> Member
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($member['gamer_tag']): ?>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div style="width: 24px; height: 24px; background: rgba(255,94,0,0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-gamepad" style="color: #FF7B33; font-size: 0.75rem;"></i>
                                                </div>
                                                <p style="margin: 0; font-size: 0.85rem; color: #a1a1aa; font-weight: 500;"><?= htmlspecialchars($member['gamer_tag']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Remove Button -->
                                    <?php if ($team['is_leader'] && $member['account_id'] != $account_id): ?>
                                        <button onclick="removeMember(<?= $member['account_id'] ?>, '<?= htmlspecialchars($member['username']) ?>')" style="position: relative; background: rgba(239,68,68,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; border-radius: 12px; padding: 0.75rem; cursor: pointer; transition: all 0.3s; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='linear-gradient(135deg, #ef4444, #dc2626)'; this.style.borderColor='transparent'; this.style.color='white'; this.style.transform='scale(1.1)'; this.style.boxShadow='0 8px 20px rgba(239,68,68,0.4)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'; this.style.borderColor='rgba(239,68,68,0.3)'; this.style.color='#fca5a5'; this.style.transform=''; this.style.boxShadow=''">
                                            <i class="fas fa-user-times" style="font-size: 0.95rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php for ($i = $team['member_count']; $i < $team['max_members']; $i++): ?>
                                <div style="background: rgba(39,39,42,0.3); backdrop-filter: blur(10px); border: 2px dashed rgba(113,113,122,0.3); border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(113,113,122,0.5)'; this.style.background='rgba(39,39,42,0.4)'" onmouseout="this.style.borderColor='rgba(113,113,122,0.3)'; this.style.background='rgba(39,39,42,0.3)'">
                                    <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(39,39,42,0.5); display: flex; align-items: center; justify-content: center; color: #71717a; font-size: 1.5rem; flex-shrink: 0; border: 1px solid rgba(113,113,122,0.2);">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0 0 0.3rem 0; font-size: 1rem; font-weight: 700; color: #a1a1aa;">Open Slot</h4>
                                        <p style="margin: 0; font-size: 0.85rem; color: #71717a; font-weight: 500;">Waiting for player to join</p>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer with Dark Theme -->
        <footer style="background: linear-gradient(135deg, #18181b, #1a1a1d); border-top: 1px solid rgba(255,255,255,0.05); padding: 2rem; text-align: center; margin-top: 3rem; position: relative; overflow: hidden;">
            <!-- Background Pattern -->
            <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>
            <p style="position: relative; margin: 0; color: #71717a; font-size: 0.875rem; font-weight: 500;">&copy; <?= date("Y") ?> Game X Community. All rights reserved.</p>
        </footer>
    </main>

    <?php if ($team['is_leader']): ?>
        <?php include '../includes/player/invite_player_modal.php'; ?>
    <?php endif; ?>
    <script>
        function openInviteModal() {
            document.getElementById('invitePlayerModal').style.display = 'flex';
        }

        function closeInviteModal() {
            document.getElementById('invitePlayerModal').style.display = 'none';
        }

        function removeMember(accountId, username) {
            Swal.fire({
                title: 'Remove Member?',
                text: `Are you sure you want to remove ${username} from the team?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff6600',
                cancelButtonColor: '#666',
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel',
                background: '#1a1a1a',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `manage_team.php?team_id=<?= $team_id ?>&remove_member=${accountId}`;
                }
            });
        }
    </script>

</body>

</html>