<?php

$available_players_query = "
    SELECT DISTINCT a.account_id, a.username, pp.gamer_tag
    FROM accounts a
    LEFT JOIN player_profiles pp ON a.account_id = pp.account_id
    WHERE a.role = 'player' 
    AND a.account_id != :current_user
    AND a.account_id NOT IN (
        SELECT tm.account_id 
        FROM team_members tm
        INNER JOIN teams t ON tm.team_id = t.team_id
        WHERE t.game_name = :game_name
    )
    AND a.account_id NOT IN (
        SELECT ti.invited_player
        FROM team_invitations ti
        INNER JOIN teams t ON ti.team_id = t.team_id
        WHERE t.game_name = :game_name2 AND ti.status = 'pending'
    )
    ORDER BY a.username ASC
";

$available_players_stmt = $conn->prepare($available_players_query);
$available_players_stmt->execute([
    'current_user' => $account_id,
    'game_name' => $team['game_name'],
    'game_name2' => $team['game_name']
]);
$available_players = $available_players_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle invitation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_invitation'])) {
    $invited_player_id = intval($_POST['invited_player']);
    
    try {
        // Check if player already has a team for this game
        $check_query = "
            SELECT COUNT(*) as count
            FROM team_members tm
            INNER JOIN teams t ON tm.team_id = t.team_id
            WHERE tm.account_id = :player_id AND t.game_name = :game_name
        ";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([
            'player_id' => $invited_player_id,
            'game_name' => $team['game_name']
        ]);
        $existing_team = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_team['count'] > 0) {
            $_SESSION['invite_error'] = "This player already has a team for " . $team['game_name'];
        } else {
            // Check if invitation already exists
            $check_invite = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM team_invitations 
                WHERE team_id = :team_id 
                AND invited_player = :player_id 
                AND status = 'pending'
            ");
            $check_invite->execute([
                'team_id' => $team_id,
                'player_id' => $invited_player_id
            ]);
            $existing_invite = $check_invite->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_invite['count'] > 0) {
                $_SESSION['invite_error'] = "Invitation already sent to this player.";
            } else {
                // Send invitation
                $invite_stmt = $conn->prepare("
                    INSERT INTO team_invitations (team_id, invited_by, invited_player, status, created_at)
                    VALUES (:team_id, :invited_by, :invited_player, 'pending', NOW())
                ");
                $invite_stmt->execute([
                    'team_id' => $team_id,
                    'invited_by' => $account_id,
                    'invited_player' => $invited_player_id
                ]);
                
                $_SESSION['invite_success'] = "Invitation sent successfully!";
                header("Location: view_team.php?team_id=" . $team_id);
                exit;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['invite_error'] = "Error sending invitation: " . $e->getMessage();
    }
}
?>

<style>
.invite-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(15px);
    align-items: center;
    justify-content: center;
}

.invite-modal-content {
    background: var(--bg-card);
    border: 3px solid var(--border);
    border-radius: 24px;
    padding: 50px;
    max-width: 600px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 30px 80px rgba(255, 94, 0, 0.4);
    animation: modalSlideIn 0.4s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.invite-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    padding-bottom: 25px;
    border-bottom: 3px solid var(--border);
}

.invite-modal-header h2 {
    font-size: 2rem;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 0;
}

.invite-modal-header h2 i {
    color: var(--accent);
    font-size: 1.8rem;
}

.close-modal {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 2.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-modal:hover {
    background: rgba(255, 94, 0, 0.1);
    color: var(--accent);
    transform: rotate(90deg);
}

.invite-form-group {
    margin-bottom: 30px;
}

.invite-form-group label {
    display: block;
    color: var(--text-main);
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.invite-form-group label i {
    color: var(--accent);
    font-size: 1.2rem;
}

.invite-form-group select {
    width: 100%;
    padding: 16px 20px;
    background: var(--bg-secondary);
    border: 2px solid var(--border);
    border-radius: 14px;
    color: var(--text-main);
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.invite-form-group select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(255, 94, 0, 0.15);
}

.invite-form-group select option {
    padding: 12px;
    background: var(--bg-card);
    color: var(--text-main);
}

.game-info-box {
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: 14px;
    margin-bottom: 25px;
    border-left: 4px solid var(--accent);
}

.game-info-box p {
    color: var(--text-muted);
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.6;
}

.game-info-box strong {
    color: var(--accent);
    font-weight: 700;
}

.no-players-message {
    text-align: center;
    padding: 50px 30px;
    background: var(--bg-secondary);
    border-radius: 14px;
    border: 2px dashed var(--border);
}

.no-players-message i {
    font-size: 4rem;
    color: var(--text-muted);
    opacity: 0.5;
    margin-bottom: 20px;
}

.no-players-message h3 {
    color: var(--text-main);
    font-size: 1.5rem;
    margin-bottom: 12px;
}

.no-players-message p {
    color: var(--text-muted);
    font-size: 1rem;
}

.invite-modal-actions {
    display: flex;
    gap: 15px;
    margin-top: 35px;
}

.btn-send-invite,
.btn-cancel-invite {
    flex: 1;
    padding: 18px 32px;
    border: none;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-send-invite {
    background: linear-gradient(135deg, var(--accent), var(--accent-hover));
    color: white;
    box-shadow: 0 6px 20px rgba(255, 94, 0, 0.4);
}

.btn-send-invite:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 94, 0, 0.6);
}

.btn-send-invite:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.btn-cancel-invite {
    background: var(--bg-secondary);
    color: var(--text-main);
    border: 2px solid var(--border);
}

.btn-cancel-invite:hover {
    background: var(--bg-main);
    border-color: var(--accent);
}
</style>

<div id="invitePlayerModal" class="invite-modal">
    <div class="invite-modal-content">
        <div class="invite-modal-header">
            <h2><i class="fas fa-user-plus"></i> Invite Player</h2>
            <button onclick="closeInviteModal()" class="close-modal">&times;</button>
        </div>

        <div class="game-info-box">
            <p><i class="fas fa-info-circle"></i> Inviting players for <strong><?= htmlspecialchars($team['game_name']) ?></strong> team. Players can only join one team per game.</p>
        </div>

        <?php if (count($available_players) > 0): ?>
            <form method="POST" action="">
                <div class="invite-form-group">
                    <label for="invited_player"><i class="fas fa-user"></i> Select Player</label>
                    <select id="invited_player" name="invited_player" required>
                        <option value="">-- Choose a Player --</option>
                        <?php foreach ($available_players as $player): ?>
                            <option value="<?= $player['account_id'] ?>">
                                <?= htmlspecialchars($player['username']) ?>
                                <?php if ($player['gamer_tag']): ?>
                                    (<?= htmlspecialchars($player['gamer_tag']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="invite-modal-actions">
                    <button type="submit" name="send_invitation" class="btn-send-invite">
                        <i class="fas fa-paper-plane"></i>
                        Send Invitation
                    </button>
                    <button type="button" onclick="closeInviteModal()" class="btn-cancel-invite">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="no-players-message">
                <i class="fas fa-users-slash"></i>
                <h3>No Available Players</h3>
                <p>All players already have teams for <?= htmlspecialchars($team['game_name']) ?> or have pending invitations.</p>
            </div>
        <?php endif; ?>
    </div>
</div>