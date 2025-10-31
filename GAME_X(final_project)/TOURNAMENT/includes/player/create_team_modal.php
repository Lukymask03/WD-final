<style>
    /* ===== CREATE TEAM MODAL ===== */
    .team-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(10px);
        align-items: center;
        justify-content: center;
    }

    .team-modal-content {
        background: var(--bg-card);
        border: 2px solid var(--border);
        border-radius: 20px;
        padding: 40px;
        max-width: 700px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(255, 94, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
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

    .team-modal-content h2 {
        color: var(--accent);
        margin-bottom: 30px;
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .team-modal-close {
        color: var(--text-muted);
        float: right;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s;
    }

    .team-modal-close:hover {
        color: var(--accent);
    }
</style>
<div id="createTeamModal" class="team-modal">
    <div class="team-modal-content">
        <span class="team-modal-close" onclick="closeCreateTeamModal()">&times;</span>
        <h2><i class="fas fa-users-cog"></i> Create New Team</h2>

        <form method="POST" enctype="multipart/form-data" id="createTeamForm">
            <input type="hidden" name="create_team" value="1">

            <div class="form-row">
                <div class="form-group">
                    <label for="team_name"><i class="fas fa-flag"></i> Team Name *</label>
                    <input type="text" id="team_name" name="team_name" required maxlength="100" placeholder="Enter team name">
                </div>

                <div class="form-group">
                    <label for="game_name"><i class="fas fa-gamepad"></i> Select Game *</label>
                    <select id="game_name" name="game_name" required>
                        <option value="">-- Choose a Game --</option>
                        <?php foreach ($available_games as $game): ?>
                            <option value="<?= htmlspecialchars($game['game_name']) ?>">
                                <?= htmlspecialchars($game['game_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="introduction"><i class="fas fa-info-circle"></i> Team Introduction</label>
                <textarea id="introduction" name="introduction" rows="4" maxlength="500" placeholder="Tell others about your team..."></textarea>
                <small>Max 500 characters</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="max_members"><i class="fas fa-users"></i> Max Members *</label>
                    <select id="max_members" name="max_members" required>
                        <option value="3">3 Members</option>
                        <option value="5" selected>5 Members</option>
                        <option value="7">7 Members</option>
                        <option value="10">10 Members</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="team_logo"><i class="fas fa-image"></i> Team Logo</label>
                    <input type="file" id="team_logo" name="team_logo" accept="image/*" onchange="previewLogo(this)">
                    <small>Recommended: 200x200px, Max 2MB</small>
                </div>
            </div>

            <div id="logoPreviewContainer" style="display: none;" class="logo-preview-container">
                <label>Logo Preview:</label>
                <img id="logoPreview" src="" alt="Logo Preview" class="logo-preview">
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-create">
                    <i class="fas fa-check-circle"></i> Create Team
                </button>
                <button type="button" onclick="closeCreateTeamModal()" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>