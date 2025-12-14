<?php
// ============================================
// invitations.php
// Player-side view of received team invitations
// ============================================

require_once(__DIR__ . '/../backend/config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'player') {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION['account_id'];

// ================================
// Handle Accept / Reject Actions
// ================================
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invitation_id = $_POST['invitation_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$invitation_id || !$action) {
        $message = "Invalid request.";
        $message_type = "error";
    } elseif ($action === 'accept') {
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
    } elseif ($action === 'reject') {
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
            $message_type = "info";
        } catch (Exception $e) {
            $message = "❌ " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// ================================
// Fetch All Invitations
// ================================
$query = "
    SELECT 
        ti.invitation_id, 
        ti.status, 
        ti.created_at, 
        t.team_name, 
        a.username AS inviter_name
    FROM team_invitations ti
    JOIN teams t ON ti.team_id = t.team_id
    JOIN accounts a ON ti.invited_by = a.account_id
    WHERE ti.invited_player = :account_id
    ORDER BY ti.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bindParam(":account_id", $account_id);
$stmt->execute();
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitations | GameX</title>
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
            <div class="gaming-hero__orb gaming-hero__orb--primary" style="background: radial-gradient(circle, rgba(16,185,129,0.15), transparent 70%);"></div>
            <div class="gaming-hero__orb gaming-hero__orb--secondary"></div>

            <!-- Floating Gradient Orbs -->
            <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(16,185,129,0.12), transparent 70%); filter: blur(60px); animation: float 15s ease-in-out infinite;"></div>
            <div style="position: absolute; bottom: -100px; left: -100px; width: 350px; height: 350px; background: radial-gradient(circle, rgba(59,130,246,0.1), transparent 70%); filter: blur(50px); animation: float 12s ease-in-out infinite;"></div>

            <!-- Gradient Overlays -->
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, transparent 0%, rgba(15,15,15,0.5) 100%);"></div>

            <!-- Content -->
            <div style="position: relative; max-width: 1200px; margin: 0 auto;">
                <!-- Badge -->
                <div style="display: inline-flex; align-items: center; gap: 0.75rem; background: rgba(39,39,42,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(16,185,129,0.3); padding: 0.65rem 1.25rem; border-radius: 50px; margin-bottom: 1.25rem;">
                    <div style="position: relative;">
                        <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: pulse 2s ease-in-out infinite;"></div>
                    </div>
                    <i class="fas fa-envelope" style="color: #10b981; font-size: 1rem;"></i>
                    <span style="color: #fafafa; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Team Invitations</span>
                </div>

                <!-- Title -->
                <h1 style="font-size: 3rem; font-weight: 900; margin: 0 0 0.75rem 0; letter-spacing: -1.5px; color: #fafafa;">
                    My Invitations
                </h1>

                <p style="font-size: 1.1rem; color: #a1a1aa; max-width: 600px;">Review and respond to team invitations from other players</p>
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

                @keyframes pulse {

                    0%,
                    100% {
                        opacity: 1;
                        transform: scale(1);
                    }

                    50% {
                        opacity: 0.6;
                        transform: scale(1.3);
                    }
                }
            </style>

            <div style="padding: 0 2rem 2rem; max-width: 1200px; margin: 0 auto;">
                <?php if (!empty($message)): ?>
                    <?php
                    $bgColor = $message_type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : ($message_type === 'error' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #3b82f6, #2563eb)');
                    $borderColor = $message_type === 'success' ? 'rgba(16,185,129,0.3)' : ($message_type === 'error' ? 'rgba(239,68,68,0.3)' : 'rgba(59,130,246,0.3)');
                    $shadowColor = $message_type === 'success' ? 'rgba(16,185,129,0.3)' : ($message_type === 'error' ? 'rgba(239,68,68,0.3)' : 'rgba(59,130,246,0.3)');
                    $icon = $message_type === 'success' ? 'fa-check-circle' : ($message_type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
                    ?>
                    <div style="background: <?= $bgColor ?>; backdrop-filter: blur(20px); border: 1px solid <?= $borderColor ?>; color: white; padding: 1.25rem 1.75rem; border-radius: 14px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 8px 24px <?= $shadowColor ?>;">
                        <i class="fas <?= $icon ?>" style="font-size: 1.5rem;"></i>
                        <span style="font-weight: 600; font-size: 1rem;"><?= $message ?></span>
                    </div>
                <?php endif; ?>

                <?php if (count($invitations) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($invitations as $invite): ?>
                            <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(16,185,129,0.3)'; this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 60px rgba(16,185,129,0.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.transform=''; this.style.boxShadow=''">
                                <!-- Background Effects -->
                                <div style="position: absolute; top: -80px; right: -80px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(16,185,129,0.08), transparent 70%); filter: blur(40px);"></div>
                                <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                                <!-- Content -->
                                <div style="position: relative; padding: 2rem;">
                                    <!-- Header -->
                                    <div style="display: flex; align-items: start; gap: 1.25rem; margin-bottom: 1.5rem;">
                                        <!-- Team Icon -->
                                        <div style="position: relative; width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.6rem; flex-shrink: 0; box-shadow: 0 8px 32px rgba(16,185,129,0.4); border: 2px solid rgba(255,255,255,0.1);">
                                            <i class="fas fa-users"></i>
                                            <!-- Pulse Ring -->
                                            <div style="position: absolute; inset: -3px; border-radius: 16px; border: 2px solid rgba(16,185,129,0.3); animation: pulse 2s ease-in-out infinite;"></div>
                                        </div>

                                        <!-- Team Info -->
                                        <div style="flex: 1; min-width: 0;">
                                            <h3 style="margin: 0 0 0.75rem 0; font-size: 1.35rem; font-weight: 800; color: #fafafa; letter-spacing: -0.5px;"><?= htmlspecialchars($invite['team_name']) ?></h3>
                                            <?php if ($invite['status'] === 'pending'): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.3); color: #fbbf24; padding: 0.4rem 0.85rem; border-radius: 10px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            <?php elseif ($invite['status'] === 'accepted'): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #10b981; padding: 0.4rem 0.85rem; border-radius: 10px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <i class="fas fa-check-circle"></i> Accepted
                                                </span>
                                            <?php else: ?>
                                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 0.4rem 0.85rem; border-radius: 10px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <i class="fas fa-times-circle"></i> Rejected
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Info Grid -->
                                    <div style="display: grid; gap: 1rem; margin-bottom: 1.5rem;">
                                        <!-- Invited By -->
                                        <div style="background: rgba(139,92,246,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(139,92,246,0.2); border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 16px rgba(139,92,246,0.3);">
                                                <i class="fas fa-user" style="color: white; font-size: 1rem;"></i>
                                            </div>
                                            <div>
                                                <div style="color: #a1a1aa; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Invited By</div>
                                                <div style="color: #fafafa; font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($invite['inviter_name']) ?></div>
                                            </div>
                                        </div>

                                        <!-- Invited On -->
                                        <div style="background: rgba(6,182,212,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(6,182,212,0.2); border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #06b6d4, #0891b2); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 16px rgba(6,182,212,0.3);">
                                                <i class="fas fa-calendar-alt" style="color: white; font-size: 1rem;"></i>
                                            </div>
                                            <div>
                                                <div style="color: #a1a1aa; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Invited On</div>
                                                <div style="color: #fafafa; font-weight: 700; font-size: 1rem;"><?= date('F j, Y', strtotime($invite['created_at'])) ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <?php if ($invite['status'] === 'pending'): ?>
                                        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: flex; gap: 1rem;">
                                            <input type="hidden" name="invitation_id" value="<?= $invite['invitation_id'] ?>">
                                            <button type="submit" name="action" value="accept" style="flex: 1; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 12px; padding: 1rem; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 8px 24px rgba(16,185,129,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 32px rgba(16,185,129,0.5)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 24px rgba(16,185,129,0.3)'">
                                                <i class="fas fa-check-circle"></i> Accept
                                            </button>
                                            <button type="submit" name="action" value="reject" style="flex: 1; background: rgba(239,68,68,0.15); backdrop-filter: blur(10px); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 1rem; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onmouseover="this.style.background='linear-gradient(135deg, #ef4444, #dc2626)'; this.style.color='white'; this.style.borderColor='transparent'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(239,68,68,0.4)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'; this.style.color='#ef4444'; this.style.borderColor='rgba(239,68,68,0.3)'; this.style.transform=''; this.style.boxShadow=''">
                                                <i class="fas fa-times-circle"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State with Complex Design -->
                    <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; padding: 4rem 2rem; text-align: center;">
                        <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>
                        <div style="position: relative; max-width: 400px; margin: 0 auto;">
                            <div style="width: 100px; height: 100px; margin: 0 auto 2rem; background: rgba(16,185,129,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative;">
                                <i class="fas fa-envelope-open" style="font-size: 3rem; color: #10b981; opacity: 0.7;"></i>
                                <div style="position: absolute; inset: -5px; border-radius: 50%; border: 2px solid rgba(16,185,129,0.2); animation: pulse 2s ease-in-out infinite;"></div>
                            </div>
                            <h3 style="color: #fafafa; font-size: 1.75rem; font-weight: 800; margin: 0 0 1rem 0; letter-spacing: -0.5px;">No Invitations</h3>
                            <p style="color: #71717a; font-size: 1.05rem; margin: 0 0 2rem 0; line-height: 1.6;">You don't have any team invitations at the moment. Browse teams to find your perfect match!</p>
                            <a href="teams.php" style="display: inline-flex; align-items: center; gap: 0.75rem; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 12px; padding: 1rem 2rem; font-weight: 700; text-decoration: none; transition: all 0.3s; box-shadow: 0 8px 24px rgba(16,185,129,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 32px rgba(16,185,129,0.5)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 24px rgba(16,185,129,0.3)'">
                                <i class="fas fa-search"></i> Browse Teams
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
    </main>
</body>

</html>