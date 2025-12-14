<?php
session_start();
require_once "../backend/db.php";

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

// Handle Accept or Reject actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $invitation_id = $_POST["invite_id"] ?? null;
    $action = $_POST["action"] ?? null;

    if (!$invitation_id || !$action) {
        $message = "Invalid request.";
        $message_type = "error";
    } elseif ($action === "accept") {
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Fetch invitation details
            $stmt = $conn->prepare("
                SELECT ti.*, t.team_name, t.game_name
                FROM team_invitations ti
                JOIN teams t ON ti.team_id = t.team_id
                WHERE ti.invitation_id = :invitation_id 
                  AND ti.invited_player = :player_id
                  AND ti.status = 'pending'
            ");
            $stmt->execute(['invitation_id' => $invitation_id, 'player_id' => $account_id]);
            $invite = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$invite) {
                throw new Exception("Invitation not found or already processed.");
            }

            // Check if player already has a team for this game
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM team_members tm
                JOIN teams t ON tm.team_id = t.team_id
                WHERE tm.account_id = :account_id 
                  AND t.game_name = :game_name
            ");
            $checkStmt->execute([
                'account_id' => $account_id,
                'game_name' => $invite['game_name']
            ]);
            $existingTeam = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingTeam['count'] > 0) {
                throw new Exception("You already have a team for " . htmlspecialchars($invite['game_name']) . ".");
            }

            // Check if player is already a member of this specific team
            $checkMemberStmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM team_members
                WHERE team_id = :team_id AND account_id = :account_id
            ");
            $checkMemberStmt->execute([
                'team_id' => $invite['team_id'],
                'account_id' => $account_id
            ]);
            $isMember = $checkMemberStmt->fetch(PDO::FETCH_ASSOC);

            if ($isMember['count'] > 0) {
                throw new Exception("You are already a member of this team.");
            }

            // Add player to team_members with 'member' role
            $insert = $conn->prepare("
                INSERT INTO team_members (team_id, account_id, role)
                VALUES (:team_id, :account_id, 'member')
            ");
            $insert->execute([
                'team_id' => $invite['team_id'],
                'account_id' => $account_id
            ]);

            // Update invitation status
            $update = $conn->prepare("
                UPDATE team_invitations 
                SET status = 'accepted' 
                WHERE invitation_id = :invitation_id
            ");
            $update->execute(['invitation_id' => $invitation_id]);

            // Commit transaction
            $conn->commit();

            $message = "✅ You have successfully joined " . htmlspecialchars($invite['team_name']) . "!";
            $message_type = "success";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $message = "❌ " . $e->getMessage();
            $message_type = "error";
        }
    } elseif ($action === "reject") {
        try {
            // Verify invitation exists and belongs to this player
            $verifyStmt = $conn->prepare("
                SELECT * FROM team_invitations 
                WHERE invitation_id = :invitation_id 
                  AND invited_player = :player_id
                  AND status = 'pending'
            ");
            $verifyStmt->execute(['invitation_id' => $invitation_id, 'player_id' => $account_id]);
            $invite = $verifyStmt->fetch(PDO::FETCH_ASSOC);

            if (!$invite) {
                throw new Exception("Invitation not found or already processed.");
            }

            // Update invitation status
            $update = $conn->prepare("
                UPDATE team_invitations 
                SET status = 'rejected' 
                WHERE invitation_id = :invitation_id
            ");
            $update->execute(['invitation_id' => $invitation_id]);

            $message = "❌ You have rejected the invitation.";
            $message_type = "error";
        } catch (Exception $e) {
            $message = "❌ " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch all pending invitations for this player
$stmt = $conn->prepare("
    SELECT 
        ti.invitation_id, 
        ti.team_id, 
        t.team_name, 
        pp.gamer_tag AS invited_by_name, 
        ti.status, 
        ti.created_at
    FROM team_invitations ti
    JOIN teams t ON ti.team_id = t.team_id
    JOIN accounts a ON ti.invited_by = a.account_id
    JOIN player_profiles pp ON a.account_id = pp.account_id
    WHERE ti.invited_player = :player_id AND ti.status = 'pending'
");
$stmt->execute(['player_id' => $account_id]);
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Respond to Invitations - GameX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="../assets/css/gaming_modern.css">
    <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body>

    <?php require_once "../includes/player/player_sidebar.php"; ?>

    <main class="org-main">
        <!-- Gaming Hero Section -->
        <section class="gaming-hero" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="gaming-hero__bg"></div>
            <div class="gaming-hero__content">
                <div class="gaming-hero__badge">
                    <i class="fas fa-envelope"></i>
                    <span>Team Invitations</span>
                </div>
                <h1 class="gaming-hero__title">Pending Invitations</h1>
                <p class="gaming-hero__subtitle">Review and respond to team invitations</p>
            </div>
        </section>

        <div class="content-section">
            <?php if (!empty($message)): ?>
                <div class="notification-item" style="margin-bottom: 2rem; border-left-color: <?= $message_type === 'success' ? '#10b981' : '#ef4444' ?>; background: <?= $message_type === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' ?>;">
                    <div class="notification-item__content">
                        <p style="margin: 0; color: var(--text-primary); font-weight: 600;"><?= $message ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (count($invitations) > 0): ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($invitations as $invite): ?>
                        <div class="glass-card" style="padding: 1.5rem;">
                            <div style="display: grid; grid-template-columns: 1fr auto; gap: 1.5rem; align-items: center;">
                                <div style="display: grid; gap: 0.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i class="fas fa-users" style="color: var(--neon-accent); font-size: 1.2rem;"></i>
                                        <h3 style="color: var(--text-primary); font-size: 1.25rem; font-weight: 700; margin: 0;"><?= htmlspecialchars($invite['team_name']) ?></h3>
                                    </div>
                                    <div style="display: flex; gap: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> Invited by: <strong style="color: var(--text-primary);"><?= htmlspecialchars($invite['invited_by_name']) ?></strong></span>
                                        <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($invite['created_at'])) ?></span>
                                    </div>
                                </div>
                                <form method="POST" style="display: flex; gap: 0.75rem;">
                                    <input type="hidden" name="invite_id" value="<?= $invite['invitation_id'] ?>">
                                    <button type="submit" name="action" value="accept" class="btn-gaming btn-gaming--success" style="padding: 0.75rem 1.5rem;">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn-gaming btn-gaming--danger" style="padding: 0.75rem 1.5rem;">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state__icon">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <h3 class="empty-state__title">No Pending Invitations</h3>
                    <p class="empty-state__message">You don't have any team invitations at the moment.</p>
                    <a href="player_dashboard.php" class="btn-gaming btn-gaming--primary">
                        <i class="fas fa-home"></i> Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>