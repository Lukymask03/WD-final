<?php
require_once "../backend/db.php";

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin (is_admin = 1)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$message_type = "";

// Handle Add Game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $game_name = trim($_POST['game_name']);
    $game_icon = trim($_POST['game_icon']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $game_image = null;

    // Handle image upload
    if (isset($_FILES['game_image']) && $_FILES['game_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['game_image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../assets/images/games/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = pathinfo($_FILES['game_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('game_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['game_image']['tmp_name'], $upload_path)) {
                $game_image = 'assets/images/games/' . $new_filename;
            }
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO games (game_name, game_icon, game_image, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$game_name, $game_icon, $game_image, $is_active]);
        $message = "Game added successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle Update Game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_game'])) {
    $game_id = $_POST['game_id'];
    $game_name = trim($_POST['game_name']);
    $game_icon = trim($_POST['game_icon']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $game_image = $_POST['existing_image'];

    // Handle new image upload
    if (isset($_FILES['game_image']) && $_FILES['game_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['game_image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../assets/images/games/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Delete old image if exists
            if ($game_image && file_exists('../' . $game_image)) {
                unlink('../' . $game_image);
            }

            $file_extension = pathinfo($_FILES['game_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('game_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['game_image']['tmp_name'], $upload_path)) {
                $game_image = 'assets/images/games/' . $new_filename;
            }
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE games SET game_name = ?, game_icon = ?, game_image = ?, is_active = ? WHERE game_id = ?");
        $stmt->execute([$game_name, $game_icon, $game_image, $is_active, $game_id]);
        $message = "Game updated successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle Delete Game
if (isset($_GET['delete'])) {
    $game_id = $_GET['delete'];
    try {
        // Get game image to delete
        $stmt = $conn->prepare("SELECT game_image FROM games WHERE game_id = ?");
        $stmt->execute([$game_id]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($game && $game['game_image'] && file_exists('../' . $game['game_image'])) {
            unlink('../' . $game['game_image']);
        }

        $stmt = $conn->prepare("DELETE FROM games WHERE game_id = ?");
        $stmt->execute([$game_id]);
        $message = "Game deleted successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Fetch all games
$stmt = $conn->query("SELECT * FROM games ORDER BY game_name ASC");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Games | Admin</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/admin_games.css">
</head>

<body>

    <?php include '../includes/admin/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h2><i class="fas fa-gamepad"></i> Manage Games</h2>
            <div class="top-bar-actions">
                <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
                <a href="../auth/logout.php" class="btn">Logout</a>
            </div>
        </div>

        <!-- Content -->
        <div class="container">
            <div class="page-header">
                <p>Add, edit, or remove games from the platform</p>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- ADD GAME FORM -->
            <div class="form-section">
                <h3><i class="fas fa-plus-circle"></i> Add New Game</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="game_name">Game Name *</label>
                        <input type="text" id="game_name" name="game_name" required>
                    </div>

                    <div class="form-group">
                        <label>Icon/Image Type *</label>
                        <div class="upload-type-toggle">
                            <button type="button" class="toggle-btn active" onclick="toggleUploadType('icon')">Font Awesome Icon</button>
                            <button type="button" class="toggle-btn" onclick="toggleUploadType('image')">Upload Image</button>
                        </div>
                    </div>

                    <div id="icon-section" class="upload-section active">
                        <div class="form-group">
                            <label for="game_icon">Game Icon (Font Awesome class) *</label>
                            <input type="text" id="game_icon" name="game_icon" placeholder="e.g., fas fa-gamepad" value="fas fa-gamepad">
                            <small style="color: var(--text-muted);">Visit <a href="https://fontawesome.com/icons" target="_blank" style="color: var(--accent);">Font Awesome</a> for icon classes</small>
                        </div>
                    </div>

                    <div id="image-section" class="upload-section">
                        <div class="form-group">
                            <label for="game_image">Game Logo/Image</label>
                            <input type="file" id="game_image" name="game_image" accept="image/*" onchange="previewImage(this, 'preview-add')">
                            <img id="preview-add" class="image-preview" style="display: none;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" checked>
                            Active
                        </label>
                    </div>

                    <button type="submit" name="add_game" class="btn-primary">
                        <i class="fas fa-plus"></i> Add Game
                    </button>
                </form>
            </div>

            <!-- GAMES LIST -->
            <h3 style="color: var(--accent); margin-bottom: 20px;"><i class="fas fa-list"></i> All Games</h3>
            <div class="games-grid">
                <?php foreach ($games as $game): ?>
                    <div class="game-card">
                        <div class="game-icon-container">
                            <?php if (!empty($game['game_image'])): ?>
                                <img src="../<?= htmlspecialchars($game['game_image']) ?>" alt="<?= htmlspecialchars($game['game_name']) ?>" class="game-image">
                            <?php else: ?>
                                <i class="<?= htmlspecialchars($game['game_icon']) ?> game-icon"></i>
                            <?php endif; ?>
                        </div>
                        <div class="game-name"><?= htmlspecialchars($game['game_name']) ?></div>
                        <div class="game-status">
                            <span class="status-badge <?= $game['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $game['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <div class="game-actions">
                            <button class="btn-edit" onclick='openEditModal(<?= json_encode($game) ?>)'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="?delete=<?= $game['game_id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this game?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3 style="color: var(--accent);"><i class="fas fa-edit"></i> Edit Game</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_game_id" name="game_id">
                <input type="hidden" id="edit_existing_image" name="existing_image">

                <div class="form-group">
                    <label for="edit_game_name">Game Name *</label>
                    <input type="text" id="edit_game_name" name="game_name" required>
                </div>

                <div class="form-group">
                    <label>Icon/Image Type *</label>
                    <div class="upload-type-toggle">
                        <button type="button" class="toggle-btn-edit active" onclick="toggleEditUploadType('icon')">Font Awesome Icon</button>
                        <button type="button" class="toggle-btn-edit" onclick="toggleEditUploadType('image')">Upload Image</button>
                    </div>
                </div>

                <div id="edit-icon-section" class="upload-section active">
                    <div class="form-group">
                        <label for="edit_game_icon">Game Icon (Font Awesome class) *</label>
                        <input type="text" id="edit_game_icon" name="game_icon">
                    </div>
                </div>

                <div id="edit-image-section" class="upload-section">
                    <div class="form-group">
                        <label for="edit_game_image">Game Logo/Image</label>
                        <input type="file" id="edit_game_image" name="game_image" accept="image/*" onchange="previewImage(this, 'preview-edit')">
                        <img id="preview-edit" class="image-preview" style="display: none;">
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="edit_is_active" name="is_active">
                        Active
                    </label>
                </div>

                <button type="submit" name="update_game" class="btn-primary">
                    <i class="fas fa-save"></i> Update Game
                </button>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Game X Community. All rights reserved.</p>
    </footer>

    <script src="../assets/js/darkmode_toggle.js"></script>
    <script>
        // Toggle upload type for add form
        function toggleUploadType(type) {
            const iconSection = document.getElementById('icon-section');
            const imageSection = document.getElementById('image-section');
            const buttons = document.querySelectorAll('.upload-type-toggle .toggle-btn');

            buttons.forEach(btn => btn.classList.remove('active'));

            if (type === 'icon') {
                iconSection.classList.add('active');
                imageSection.classList.remove('active');
                buttons[0].classList.add('active');
                document.getElementById('game_icon').required = true;
                document.getElementById('game_image').required = false;
            } else {
                iconSection.classList.remove('active');
                imageSection.classList.add('active');
                buttons[1].classList.add('active');
                document.getElementById('game_icon').required = false;
                document.getElementById('game_image').required = true;
            }
        }

        // Toggle upload type for edit form
        function toggleEditUploadType(type) {
            const iconSection = document.getElementById('edit-icon-section');
            const imageSection = document.getElementById('edit-image-section');
            const buttons = document.querySelectorAll('.upload-type-toggle .toggle-btn-edit');

            buttons.forEach(btn => btn.classList.remove('active'));

            if (type === 'icon') {
                iconSection.classList.add('active');
                imageSection.classList.remove('active');
                buttons[0].classList.add('active');
            } else {
                iconSection.classList.remove('active');
                imageSection.classList.add('active');
                buttons[1].classList.add('active');
            }
        }

        // Preview image
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Open edit modal
        function openEditModal(game) {
            document.getElementById('edit_game_id').value = game.game_id;
            document.getElementById('edit_game_name').value = game.game_name;
            document.getElementById('edit_game_icon').value = game.game_icon || 'fas fa-gamepad';
            document.getElementById('edit_existing_image').value = game.game_image || '';
            document.getElementById('edit_is_active').checked = game.is_active == 1;

            // Show current image if exists
            const previewEdit = document.getElementById('preview-edit');
            if (game.game_image) {
                previewEdit.src = '../' + game.game_image;
                previewEdit.style.display = 'block';
                toggleEditUploadType('image');
            } else {
                previewEdit.style.display = 'none';
                toggleEditUploadType('icon');
            }

            document.getElementById('editModal').style.display = 'block';
        }

        // Close edit modal
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>

</body>

</html>