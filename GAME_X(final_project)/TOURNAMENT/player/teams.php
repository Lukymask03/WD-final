<?php
session_start();
require_once "../backend/db.php";

// Security check
if (!isset($_SESSION["account_id"]) || $_SESSION["role"] !== "player") {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION["account_id"];

// Handle team creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_team'])) {
    $team_name = trim($_POST['team_name']);
    $game_name = trim($_POST['game_name']);
    $introduction = trim($_POST['introduction']);
    $max_members = intval($_POST['max_members']);

    // Handle logo upload
    $team_logo = null;
    if (isset($_FILES['team_logo']) && $_FILES['team_logo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['team_logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = 'team_' . uniqid() . '.' . $ext;
            $upload_path = '../assets/images/teams/' . $new_filename;

            if (!file_exists('../assets/images/teams/')) {
                mkdir('../assets/images/teams/', 0777, true);
            }

            if (move_uploaded_file($_FILES['team_logo']['tmp_name'], $upload_path)) {
                $team_logo = 'assets/images/teams/' . $new_filename;
            }
        }
    }

    try {
        // Insert team
        $stmt = $conn->prepare("
            INSERT INTO teams (team_name, game_name, introduction, team_logo, max_members, created_by, created_at)
            VALUES (:team_name, :game_name, :introduction, :team_logo, :max_members, :created_by, NOW())
        ");
        $stmt->execute([
            'team_name' => $team_name,
            'game_name' => $game_name,
            'introduction' => $introduction,
            'team_logo' => $team_logo,
            'max_members' => $max_members,
            'created_by' => $account_id
        ]);

        $team_id = $conn->lastInsertId();

        // Add creator as team leader
        $stmt = $conn->prepare("
            INSERT INTO team_members (team_id, account_id, role, joined_at)
            VALUES (:team_id, :account_id, 'leader', NOW())
        ");
        $stmt->execute([
            'team_id' => $team_id,
            'account_id' => $account_id
        ]);

        $_SESSION['team_success'] = "Team created successfully! You are now the team leader.";
        header("Location: view_team.php?team_id=" . $team_id);
        exit;
    } catch (PDOException $e) {
        $_SESSION['team_error'] = "Error creating team: " . $e->getMessage();
    }
}

// Get selected game filter (default: all)
$selected_game = $_GET['game'] ?? 'all';

// Fetch ALL games from the games table (even if no teams exist)
$games_query = "
    SELECT game_id, game_name, game_icon, game_image, is_active
    FROM games
    WHERE is_active = 1
    ORDER BY game_name ASC
";
$games_stmt = $conn->query($games_query);
$available_games = $games_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch teams based on filter
if ($selected_game === 'all') {
    $teams_query = "
        SELECT 
            t.team_id,
            t.team_name,
            t.game_name,
            t.team_logo,
            t.max_members,
            t.created_at,
            a.username AS creator_name,
            (SELECT COUNT(*) FROM team_members WHERE team_id = t.team_id) AS member_count,
            CASE WHEN t.created_by = :account_id THEN 1 ELSE 0 END AS is_owner,
            CASE WHEN tm.account_id IS NOT NULL THEN 1 ELSE 0 END AS is_member
        FROM teams t
        LEFT JOIN accounts a ON t.created_by = a.account_id
        LEFT JOIN team_members tm ON t.team_id = tm.team_id AND tm.account_id = :account_id2
        ORDER BY t.created_at DESC
    ";
    $teams_stmt = $conn->prepare($teams_query);
    $teams_stmt->execute(['account_id' => $account_id, 'account_id2' => $account_id]);
} else {
    $teams_query = "
        SELECT 
            t.team_id,
            t.team_name,
            t.game_name,
            t.team_logo,
            t.max_members,
            t.created_at,
            a.username AS creator_name,
            (SELECT COUNT(*) FROM team_members WHERE team_id = t.team_id) AS member_count,
            CASE WHEN t.created_by = :account_id THEN 1 ELSE 0 END AS is_owner,
            CASE WHEN tm.account_id IS NOT NULL THEN 1 ELSE 0 END AS is_member
        FROM teams t
        LEFT JOIN accounts a ON t.created_by = a.account_id
        LEFT JOIN team_members tm ON t.team_id = tm.team_id AND tm.account_id = :account_id2
        WHERE t.game_name = :game_name
        ORDER BY t.created_at DESC
    ";
    $teams_stmt = $conn->prepare($teams_query);
    $teams_stmt->execute([
        'account_id' => $account_id,
        'account_id2' => $account_id,
        'game_name' => $selected_game
    ]);
}

$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check which games the player already has teams for
$player_teams_query = "SELECT DISTINCT game_name FROM teams WHERE created_by = :account_id";
$player_teams_stmt = $conn->prepare($player_teams_query);
$player_teams_stmt->execute(['account_id' => $account_id]);
$player_game_teams = $player_teams_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Teams - GameX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="../assets/css/gaming_modern.css">
    <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body style="background: var(--bg-primary); min-height: 100vh;">
    <?php include '../includes/player/player_sidebar.php'; ?>

    <main class="org-main">
        <!-- Complex Hero Section with Layered Background -->
        <section style="position: relative; padding: 3rem 2rem; margin-bottom: 2rem; overflow: hidden; border-radius: 24px; background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0f0f0f 100%);">
            <!-- Animated Background Layer 1: Grid Pattern -->
            <div style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,94,0,0.03) 2px, transparent 2px), linear-gradient(90deg, rgba(255,94,0,0.03) 2px, transparent 2px); background-size: 50px 50px; animation: gridMove 20s linear infinite;"></div>

            <!-- Animated Background Layer 2: Gradient Orbs -->
            <div style="position: absolute; top: -50%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(255,94,0,0.15), transparent 70%); border-radius: 50%; filter: blur(60px); animation: float 15s ease-in-out infinite;"></div>
            <div style="position: absolute; bottom: -30%; right: -5%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(139,92,246,0.1), transparent 70%); border-radius: 50%; filter: blur(50px); animation: float 12s ease-in-out infinite reverse;"></div>

            <!-- Content Container -->
            <div style="position: relative; z-index: 1;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
                    <!-- Left Content -->
                    <div style="flex: 1; min-width: 300px;">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.25rem; background: rgba(255,94,0,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,94,0,0.3); border-radius: 50px; margin-bottom: 1rem;">
                            <div style="width: 8px; height: 8px; background: #FF5E00; border-radius: 50%; animation: pulse 2s ease-in-out infinite;"></div>
                            <span style="color: #FF5E00; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Team Discovery</span>
                        </div>
                        <h1 style="font-size: 2.5rem; font-weight: 800; margin: 0 0 1rem 0; background: linear-gradient(135deg, #ffffff 0%, #a1a1aa 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1.2;">Explore Elite<br />Gaming Teams</h1>
                        <p style="color: #a1a1aa; font-size: 1rem; line-height: 1.6; max-width: 500px; margin: 0;">Connect with competitive teams, join forces with skilled players, and dominate the tournament scene together.</p>

                        <!-- Stats Row -->
                        <div style="display: flex; gap: 2rem; margin-top: 1.5rem;">
                            <div>
                                <div style="font-size: 2rem; font-weight: 800; color: #FF5E00;"><?= count($teams) ?></div>
                                <div style="font-size: 0.85rem; color: #71717a; text-transform: uppercase; letter-spacing: 0.5px;">Active Teams</div>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: 800; color: #8b5cf6;"><?= count($available_games) ?></div>
                                <div style="font-size: 0.85rem; color: #71717a; text-transform: uppercase; letter-spacing: 0.5px;">Games</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Actions -->
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <a href="my_teams.php" style="padding: 1rem 2rem; background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fafafa; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateX(4px)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.transform=''">
                            <i class="fas fa-users-cog" style="font-size: 1.2rem;"></i>
                            <div style="text-align: left;">
                                <div style="font-size: 0.75rem; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.5px;">View</div>
                                <div>My Teams</div>
                            </div>
                            <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 0.9rem;"></i>
                        </a>
                        <button onclick="openCreateTeamModal()" style="padding: 1rem 2rem; background: linear-gradient(135deg, #FF5E00, #FF7B33); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 8px 24px rgba(255,94,0,0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 32px rgba(255,94,0,0.4)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 24px rgba(255,94,0,0.3)'">
                            <i class="fas fa-plus-circle" style="font-size: 1.2rem;"></i>
                            <div style="text-align: left;">
                                <div style="font-size: 0.75rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Create</div>
                                <div>New Team</div>
                            </div>
                        </button>
                    </div>
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

        <!-- Content Area -->
        <div style="padding: 2rem;">
            <!-- Complex Game Filter with Glassmorphism -->
            <div style="position: relative; background: rgba(24,24,27,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 2rem; margin-bottom: 2rem; overflow: hidden;">
                <!-- Background Accent -->
                <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,94,0,0.1), transparent); border-radius: 50%; filter: blur(40px);"></div>

                <div style="position: relative; z-index: 1;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="width: 4px; height: 28px; background: linear-gradient(to bottom, #FF5E00, #FF7B33); border-radius: 2px;"></div>
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: #fafafa; margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-sliders-h" style="color: #FF5E00;"></i> Game Filter
                            <span style="font-size: 0.8rem; color: #71717a; font-weight: 500;">/ Select your game</span>
                        </h3>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <button onclick="filterByGame('all')"
                            style="position: relative; padding: 1rem 1.75rem; border-radius: 12px; border: <?= $selected_game === 'all' ? '2px solid #FF5E00' : '1px solid rgba(255,255,255,0.1)' ?>; background: <?= $selected_game === 'all' ? 'linear-gradient(135deg, #FF5E00, #FF7B33)' : 'rgba(39,39,42,0.5)' ?>; backdrop-filter: blur(10px); color: <?= $selected_game === 'all' ? 'white' : '#fafafa' ?>; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.75rem; box-shadow: <?= $selected_game === 'all' ? '0 8px 24px rgba(255,94,0,0.3), inset 0 1px 0 rgba(255,255,255,0.2)' : 'none' ?>;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#FF5E00'" onmouseout="this.style.transform=''; this.style.borderColor='<?= $selected_game === 'all' ? '#FF5E00' : 'rgba(255,255,255,0.1)' ?>'">
                            <?php if ($selected_game === 'all'): ?>
                                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent); border-radius: 12px; pointer-events: none;"></div>
                            <?php endif; ?>
                            <i class="fas fa-gamepad" style="font-size: 1.1rem;"></i>
                            <div style="display: flex; flex-direction: column; align-items: start;">
                                <span style="font-size: 0.7rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">View</span>
                                <span>All Games</span>
                            </div>
                            <span style="background: <?= $selected_game === 'all' ? 'rgba(255,255,255,0.25)' : 'rgba(255,94,0,0.2)' ?>; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 700; border: 1px solid <?= $selected_game === 'all' ? 'rgba(255,255,255,0.3)' : 'rgba(255,94,0,0.3)' ?>;"><?= count($teams) ?></span>
                        </button>

                        <?php foreach ($available_games as $game):
                            $count_query = "SELECT COUNT(*) as count FROM teams WHERE game_name = :game_name";
                            $count_stmt = $conn->prepare($count_query);
                            $count_stmt->execute(['game_name' => $game['game_name']]);
                            $team_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            $is_active = $selected_game === $game['game_name'];
                        ?>
                            <button onclick="filterByGame('<?= htmlspecialchars($game['game_name']) ?>')"
                                style="position: relative; padding: 1rem 1.75rem; border-radius: 12px; border: <?= $is_active ? '2px solid #FF5E00' : '1px solid rgba(255,255,255,0.1)' ?>; background: <?= $is_active ? 'linear-gradient(135deg, #FF5E00, #FF7B33)' : 'rgba(39,39,42,0.5)' ?>; backdrop-filter: blur(10px); color: <?= $is_active ? 'white' : '#fafafa' ?>; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.75rem; box-shadow: <?= $is_active ? '0 8px 24px rgba(255,94,0,0.3), inset 0 1px 0 rgba(255,255,255,0.2)' : 'none' ?>;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#FF5E00'" onmouseout="this.style.transform=''; this.style.borderColor='<?= $is_active ? '#FF5E00' : 'rgba(255,255,255,0.1)' ?>'">
                                <?php if ($is_active): ?>
                                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent); border-radius: 12px; pointer-events: none;"></div>
                                <?php endif; ?>
                                <?php if (in_array($game['game_name'], $player_game_teams)): ?>
                                    <div style="position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #18181b; box-shadow: 0 2px 8px rgba(251,191,36,0.4);">
                                        <i class="fas fa-crown" style="font-size: 0.65rem; color: white;"></i>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($game['game_icon'])): ?>
                                    <i class="<?= htmlspecialchars($game['game_icon']) ?>" style="font-size: 1.1rem;"></i>
                                <?php else: ?>
                                    <i class="fas fa-gamepad" style="font-size: 1.1rem;"></i>
                                <?php endif; ?>
                                <div style="display: flex; flex-direction: column; align-items: start;">
                                    <span style="font-size: 0.7rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Game</span>
                                    <span><?= htmlspecialchars($game['game_name']) ?></span>
                                </div>
                                <span style="background: <?= $is_active ? 'rgba(255,255,255,0.25)' : 'rgba(255,94,0,0.2)' ?>; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 700; border: 1px solid <?= $is_active ? 'rgba(255,255,255,0.3)' : 'rgba(255,94,0,0.3)' ?>;"><?= $team_count ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Complex Teams Grid with Advanced Styling -->
            <?php if (count($teams) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($teams as $team): ?>
                        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onmouseover="this.style.borderColor='rgba(255,94,0,0.5)'; this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,94,0,0.2)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.transform=''; this.style.boxShadow=''">
                            <!-- Accent Glow -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, #FF5E00, transparent); opacity: 0; transition: opacity 0.3s;"></div>

                            <!-- Background Pattern -->
                            <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                            <!-- Team Header with Complex Layout -->
                            <div style="position: relative; padding: 1.75rem; background: linear-gradient(135deg, rgba(255,94,0,0.05) 0%, transparent 100%);">
                                <div style="display: flex; align-items: start; gap: 1.25rem;">
                                    <!-- Logo with Glow Effect -->
                                    <?php if (!empty($team['team_logo'])): ?>
                                        <div style="position: relative; flex-shrink: 0;">
                                            <div style="width: 70px; height: 70px; border-radius: 12px; overflow: hidden; border: 2px solid rgba(255,94,0,0.3); box-shadow: 0 8px 24px rgba(0,0,0,0.3); position: relative; z-index: 1;">
                                                <img src="../<?= htmlspecialchars($team['team_logo']) ?>" alt="<?= htmlspecialchars($team['team_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <!-- Glow effect behind logo -->
                                            <div style="position: absolute; inset: -10px; background: radial-gradient(circle, rgba(255,94,0,0.2), transparent 70%); filter: blur(15px); z-index: 0;"></div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Team Info -->
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem;">
                                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #fafafa; line-height: 1.3; letter-spacing: -0.5px;">
                                                <?= htmlspecialchars($team['team_name']) ?>
                                            </h3>
                                            <?php if ($team['is_owner']): ?>
                                                <span style="flex-shrink: 0; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 12px rgba(16,185,129,0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <i class="fas fa-crown" style="font-size: 0.7rem;"></i> Owner
                                                </span>
                                            <?php elseif ($team['is_member']): ?>
                                                <span style="flex-shrink: 0; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 12px rgba(59,130,246,0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <i class="fas fa-check-circle" style="font-size: 0.7rem;"></i> Member
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Game Tag -->
                                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.9rem; background: rgba(255,94,0,0.1); border: 1px solid rgba(255,94,0,0.2); border-radius: 6px;">
                                            <i class="fas fa-gamepad" style="color: #FF5E00; font-size: 0.8rem;"></i>
                                            <span style="color: #FF5E00; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($team['game_name']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Section with Glassmorphism -->
                            <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.25rem;">
                                    <!-- Members Stat -->
                                    <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(255,94,0,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #FF5E00, #FF7B33); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255,94,0,0.3);">
                                                <i class="fas fa-users" style="color: white; font-size: 0.9rem;"></i>
                                            </div>
                                            <div style="flex: 1;">
                                                <div style="font-size: 0.7rem; color: #71717a; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.2rem;">Members</div>
                                                <div style="font-size: 1.1rem; color: #fafafa; font-weight: 700;"><?= $team['member_count'] ?><span style="color: #71717a; font-size: 0.9rem; font-weight: 500;"> / <?= $team['max_members'] ?></span></div>
                                            </div>
                                        </div>
                                        <!-- Progress Bar -->
                                        <div style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                                            <div style="height: 100%; background: linear-gradient(90deg, #FF5E00, #FF7B33); width: <?= ($team['member_count'] / $team['max_members']) * 100 ?>%; transition: width 0.3s; box-shadow: 0 0 10px rgba(255,94,0,0.5);"></div>
                                        </div>
                                    </div>

                                    <!-- Leader Info -->
                                    <div style="background: rgba(39,39,42,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(139,92,246,0.3)'; this.style.background='rgba(39,39,42,0.8)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(39,39,42,0.6)'">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(139,92,246,0.3);">
                                                <i class="fas fa-user-shield" style="color: white; font-size: 0.9rem;"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-size: 0.7rem; color: #71717a; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.2rem;">Leader</div>
                                                <div style="font-size: 0.9rem; color: #fafafa; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($team['creator_name']) ?>"><?= htmlspecialchars($team['creator_name']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button with Hover Effect -->
                                <a href="view_team.php?team_id=<?= $team['team_id'] ?>" style="position: relative; display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; padding: 1rem; background: linear-gradient(135deg, #FF5E00, #FF7B33); color: white; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.9rem; transition: all 0.3s; overflow: hidden; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 16px rgba(255,94,0,0.3); border: 1px solid rgba(255,255,255,0.1);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(255,94,0,0.5)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 16px rgba(255,94,0,0.3)'">
                                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent); pointer-events: none;"></div>
                                    <span style="position: relative;">View Team Details</span>
                                    <i class="fas fa-arrow-right" style="position: relative; font-size: 0.9rem; transition: transform 0.3s;" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform=''"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="org-empty-state">
                    <div class="org-empty-icon">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h3>No Teams Found</h3>
                    <p>
                        <?php if ($selected_game === 'all'): ?>
                            No teams have been created yet. Be the first to create one!
                        <?php else: ?>
                            No teams found for <strong><?= htmlspecialchars($selected_game) ?></strong>. Create one now!
                        <?php endif; ?>
                    </p>
                    <button onclick="openCreateTeamModal()" class="org-btn org-btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        Create First Team
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Create Team Modal -->
    <?php include '../includes/player/create_team_modal.php'; ?>

    <footer class="footer">
        <p>&copy; <?= date("Y") ?> Game X Community. All rights reserved.</p>
    </footer>
    </div>

    <script src="../assets/js/darkmode_toggle.js"></script>
    <script src="../assets/js/index.js"></script>
    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <script>
        function filterByGame(game) {
            window.location.href = 'teams.php?game=' + encodeURIComponent(game);
        }

        function openCreateTeamModal() {
            document.getElementById('createTeamModal').style.display = 'flex';
        }

        function closeCreateTeamModal() {
            document.getElementById('createTeamModal').style.display = 'none';
        }

        // Preview logo
        function previewLogo(input) {
            const preview = document.getElementById('logoPreview');
            const previewContainer = document.getElementById('logoPreviewContainer');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('createTeamModal');
            if (event.target === modal) {
                closeCreateTeamModal();
            }
        }

        // Success/Error alerts
        <?php if (isset($_SESSION['team_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Team Created! 🎮',
                text: '<?= $_SESSION['team_success'] ?>',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#ff6600'
            });
            <?php unset($_SESSION['team_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['team_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= $_SESSION['team_error'] ?>',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#ff6600'
            });
            <?php unset($_SESSION['team_error']); ?>
        <?php endif; ?>
    </script>

</body>

</html>