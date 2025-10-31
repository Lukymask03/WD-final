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
    <title>Teams - GameX</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/teams.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/sweetalert2.min.css">
</head>

<body>

    <?php require_once "../includes/player/player_navbar.php"; ?>

    <main class="teams-container">
        <div class="teams-header">
            <div class="header-left">
                <h1>Game Teams</h1>
                <p class="header-subtitle">Browse and join teams across different games</p>
            </div>
            <div class="header-actions">
                <a href="my_teams.php" class="my-teams-btn">
                    <i class="fas fa-users"></i>
                    <span>My Teams</span>
                </a>
                <button onclick="openCreateTeamModal()" class="create-team-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create Team</span>
                </button>
            </div>
        </div>

        <!-- Game Filter Tabs -->
        <div class="game-filters">
            <button class="filter-btn <?= $selected_game === 'all' ? 'active' : '' ?>"
                onclick="filterByGame('all')">
                <div class="filter-icon">
                    <i class="fa fa-gamepad"></i>
                </div>
                <span class="filter-label">All Games</span>
                <span class="filter-count"><?= count($teams) ?></span>
            </button>

            <?php foreach ($available_games as $game):
                // Count teams for this game
                $count_query = "SELECT COUNT(*) as count FROM teams WHERE game_name = :game_name";
                $count_stmt = $conn->prepare($count_query);
                $count_stmt->execute(['game_name' => $game['game_name']]);
                $team_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
            ?>
                <button class="filter-btn <?= $selected_game === $game['game_name'] ? 'active' : '' ?>"
                    onclick="filterByGame('<?= htmlspecialchars($game['game_name']) ?>')">
                    <div class="filter-icon">
                        <?php if (!empty($game['game_image'])): ?>
                            <img src="../<?= htmlspecialchars($game['game_image']) ?>" alt="<?= htmlspecialchars($game['game_name']) ?>">
                        <?php elseif (!empty($game['game_icon'])): ?>
                            <i class="<?= htmlspecialchars($game['game_icon']) ?>"></i>
                        <?php else: ?>
                            <i class="fas fa-gamepad"></i>
                        <?php endif; ?>
                    </div>
                    <span class="filter-label"><?= htmlspecialchars($game['game_name']) ?></span>
                    <span class="filter-count"><?= $team_count ?></span>
                    <?php if (in_array($game['game_name'], $player_game_teams)): ?>
                        <span class="owned-badge">
                            <i class="fas fa-crown"></i>
                        </span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Teams Grid -->
        <div class="teams-grid">
            <div class="teams-grid">
                <?php if (count($teams) > 0): ?>
                    <?php foreach ($teams as $team): ?>
                        <div class="team-card <?= $team['is_member'] ? 'member-card' : '' ?>">
                            <!-- Team Logo -->
                            <?php if (!empty($team['team_logo'])): ?>
                                <div class="team-logo-header">
                                    <img src="../<?= htmlspecialchars($team['team_logo']) ?>" alt="<?= htmlspecialchars($team['team_name']) ?>">
                                </div>
                            <?php else: ?>
                                <div class="team-logo-placeholder">
                                    <i class="fas fa-users"></i>
                                </div>
                            <?php endif; ?>

                            <div class="team-card-header">
                                <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                                <?php if ($team['is_owner']): ?>
                                    <span class="owner-badge">
                                        <i class="fas fa-crown"></i> Owner
                                    </span>
                                <?php elseif ($team['is_member']): ?>
                                    <span class="member-badge">
                                        <i class="fas fa-user-check"></i> Member
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="team-info">
                                <p>
                                    <span class="game-name">
                                        <i class="fas fa-gamepad"></i>
                                        <?= htmlspecialchars($team['game_name']) ?>
                                    </span>
                                    <span class="creator">
                                        <i class="fas fa-user"></i>
                                        <?= htmlspecialchars($team['creator_name']) ?>
                                    </span>
                                </p>
                            </div>

                            <div class="team-actions">
                                <a href="view_team.php?team_id=<?= $team['team_id'] ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-teams">
                        <div class="no-teams-icon">
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <h3>No Teams Found</h3>
                        <p>
                            <?php if ($selected_game === 'all'): ?>
                                No teams have been created yet. Be the first to create one!
                            <?php else: ?>
                                No teams found for <strong><?= htmlspecialchars($selected_game) ?></strong>.
                            <?php endif; ?>
                        </p>
                        <button onclick="openCreateTeamModal()" class="btn-create-first">
                            <i class="fas fa-plus-circle"></i>
                            Create Your First Team
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

    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <script src="../assets/js/darkmode_toggle.js"></script>
    <script src="../assets/js/index.js"></script>
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