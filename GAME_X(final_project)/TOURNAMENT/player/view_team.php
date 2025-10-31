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
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/view-teams.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/sweetalert2.min.css">
</head>

<body>

    <?php require_once "../includes/player/player_navbar.php"; ?>

    <main class="team-view-container">
        <!-- Full Width Hero Banner -->
        <div class="team-header-banner" data-game="<?= htmlspecialchars($team['game_name']) ?>">
            <div class="banner-overlay"></div>
            
            <div class="banner-content">
                <?php if ($team['team_logo']): ?>
                    <div class="team-logo-large">
                        <img src="../<?= htmlspecialchars($team['team_logo']) ?>" alt="<?= htmlspecialchars($team['team_name']) ?>">
                    </div>
                <?php endif; ?>
                
                <div class="banner-info">
                    <h1 class="team-title"><?= htmlspecialchars($team['team_name']) ?></h1>
                    
                    <?php if ($team['is_leader']): ?>
                        <div class="banner-actions">
                            <a href="manage_team.php?team_id=<?= $team_id ?>" class="btn-manage-header">
                                <i class="fas fa-cog"></i> Manage Team
                            </a>
                            <button onclick="openInviteModal()" class="btn-invite-header">
                                <i class="fas fa-user-plus"></i> Invite Player
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Team Details Section (Below Banner) -->
        <div class="team-details-section">
            <div class="details-container">
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="detail-content">
                        <span class="detail-label">Game</span>
                        <span class="detail-value"><?= htmlspecialchars($team['game_name']) ?></span>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="detail-content">
                        <span class="detail-label">Created By</span>
                        <span class="detail-value"><?= htmlspecialchars($team['creator_name']) ?></span>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="detail-content">
                        <span class="detail-label">Members</span>
                        <span class="detail-value"><?= $team['member_count'] ?> / <?= $team['max_members'] ?></span>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="far fa-calendar-alt"></i>
                    </div>
                    <div class="detail-content">
                        <span class="detail-label">Created</span>
                        <span class="detail-value"><?= date('M d, Y', strtotime($team['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($team['introduction']): ?>
            <div class="team-introduction-section">
                <h2><i class="fas fa-info-circle"></i> About This Team</h2>
                <p><?= nl2br(htmlspecialchars($team['introduction'])) ?></p>
            </div>
        <?php endif; ?>

        <div class="team-members-section">
            <h2><i class="fas fa-users"></i> Team Members (<?= count($members) ?>)</h2>
            <div class="members-grid">
                <?php foreach ($members as $member): ?>
                    <div class="member-card">
                        <div class="member-avatar">
                            <?= strtoupper(substr($member['username'], 0, 2)) ?>
                        </div>
                        <div class="member-info">
                            <h3><?= htmlspecialchars($member['username']) ?></h3>
                            <?php if ($member['gamer_tag']): ?>
                                <p class="gamer-tag"><i class="fas fa-gamepad"></i> <?= htmlspecialchars($member['gamer_tag']) ?></p>
                            <?php endif; ?>
                            <div class="member-role">
                                <?php if ($member['role'] === 'leader'): ?>
                                    <span class="role-badge leader"><i class="fas fa-crown"></i> Leader</span>
                                <?php else: ?>
                                    <span class="role-badge member"><i class="fas fa-user"></i> Member</span>
                                <?php endif; ?>
                            </div>
                            <p class="joined-date"><i class="far fa-calendar"></i> Joined <?= date('M d, Y', strtotime($member['joined_at'])) ?></p>
                        </div>
                        
                        <?php if ($team['is_leader'] && $member['account_id'] != $account_id): ?>
                            <button onclick="removeMember(<?= $member['account_id'] ?>, '<?= htmlspecialchars($member['username']) ?>')" class="btn-remove-member">
                                <i class="fas fa-user-times"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php for ($i = $team['member_count']; $i < $team['max_members']; $i++): ?>
                    <div class="member-card empty-slot">
                        <div class="member-avatar empty">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="member-info">
                            <h3>Open Slot</h3>
                            <p>Looking for players</p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="back-action">
            <a href="teams.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to All Teams
            </a>
        </div>
    </main>

    <!-- Invite Player Modal -->
    <?php if ($team['is_leader']): ?>
        <?php include '../includes/player/invite_player_modal.php'; ?>
    <?php endif; ?>

    <footer class="footer">
        <p>&copy; <?= date("Y") ?> Game X Community. All rights reserved.</p>
    </footer>

    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <script src="../assets/js/darkmode_toggle.js"></script>
    <script src="../assets/js/index.js"></script>
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