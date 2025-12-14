<?php
// ========================================
// ORGANIZER TOURNAMENT MANAGEMENT
// ========================================

session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/auth_guard.php';

checkAuth('organizer');

// ===== Get organizer_id from organizer_profiles table =====
$stmtProfile = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
$stmtProfile->execute([$_SESSION['account_id']]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Organizer profile not found. Please complete your profile first.");
}

$organizer_id = $profile['organizer_id']; // ✅ Correct organizer_id

// Organizer Sidebar
$sidebarPath = __DIR__ . '/../includes/organizer/organizer_sidebar.php';
if (!file_exists($sidebarPath)) {
    die("Sidebar file missing at: " . htmlspecialchars($sidebarPath));
}

// ----------------------------
// Pagination & Search Setup
// ----------------------------
$pageSize = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $pageSize;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {

    // ⭐ Count tournaments created by this organizer (case-insensitive search)
    if (!empty($search)) {
        $countStmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM tournaments 
            WHERE organizer_id = :oid AND LOWER(title) LIKE LOWER(:search)
        ");
        $countStmt->execute([
            ':oid' => $organizer_id,
            ':search' => "%$search%"
        ]);
    } else {
        $countStmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM tournaments 
            WHERE organizer_id = :oid
        ");
        $countStmt->execute([':oid' => $organizer_id]);
    }

    $totalTournaments = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalTournaments / $pageSize));

    // ⭐ Fetch tournaments created by organizer (case-insensitive search)
    if (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT * FROM tournaments
            WHERE organizer_id = :oid AND LOWER(title) LIKE LOWER(:search)
            ORDER BY start_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    } else {
        $stmt = $conn->prepare("
            SELECT * FROM tournaments
            WHERE organizer_id = :oid
            ORDER BY start_date DESC
            LIMIT :limit OFFSET :offset
        ");
    }

    $stmt->bindValue(':oid', $organizer_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tournaments - Game X</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Organizer CSS -->
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
</head>

<body>
    <?php include $sidebarPath; ?>

    <main class="org-main">
        <div class="org-table-container">
            <div class="org-table-header">
                <div>
                    <h3 class="org-table-title"><i class="fas fa-trophy"></i> My Tournaments</h3>
                    <p style="color: #666; margin-top: 5px; font-size: 14px;">Manage and oversee all your created tournaments</p>
                </div>
                <a href="create_tournament.php" class="org-btn">
                    <i class="fas fa-plus-circle"></i> Create New Tournament
                </a>
            </div>

            <!-- Search Bar -->
            <div style="padding: 0 30px 20px;">
                <form method="GET" class="org-search-bar">
                    <input type="text" name="search" class="org-search-input"
                        placeholder="Search tournaments by name..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="org-search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="view_tournaments.php" class="org-btn org-btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <table class="org-data-table">
                <thead>
                    <tr>
                        <th>Tournament Name</th>
                        <th>Game</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Max Teams</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($tournaments): ?>
                        <?php foreach ($tournaments as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['title']) ?></strong></td>
                                <td><?= htmlspecialchars($t['game']) ?></td>
                                <td><?= date('M d, Y', strtotime($t['start_date'])) ?></td>
                                <td><?= $t['end_date'] ? date('M d, Y', strtotime($t['end_date'])) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($t['max_teams']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch ($t['status']) {
                                        case 'open':
                                            $statusClass = 'org-badge-success';
                                            $statusText = 'OPEN';
                                            break;
                                        case 'completed':
                                            $statusClass = 'org-badge-info';
                                            $statusText = 'COMPLETED';
                                            break;
                                        case 'closed':
                                            $statusClass = 'org-badge-danger';
                                            $statusText = 'CLOSED';
                                            break;
                                        default:
                                            $statusClass = 'org-badge-warning';
                                            $statusText = strtoupper($t['status']);
                                    }
                                    ?>
                                    <span class="org-badge <?= $statusClass ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="org-action-btns">
                                        <a href="view_single_tournament.php?id=<?= $t['tournament_id'] ?>"
                                            class="org-action-btn org-action-view" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>

                                        <a href="edit_tournament.php?id=<?= $t['tournament_id'] ?>"
                                            class="org-action-btn org-action-edit" title="Edit Tournament">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <a href="../backend/delete_tournament.php?id=<?= $t['tournament_id'] ?>"
                                            onclick="return confirm('Are you sure you want to delete this tournament? This action cannot be undone.');"
                                            class="org-action-btn org-action-delete"
                                            title="Delete Tournament">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="org-empty-state">
                                    <i class="fas fa-trophy"></i>
                                    <?php if (!empty($search)): ?>
                                        <h3>No tournaments found</h3>
                                        <p>No tournaments match "<?= htmlspecialchars($search) ?>". Try a different search term.</p>
                                    <?php else: ?>
                                        <h3>No tournaments yet</h3>
                                        <p>You haven't created any tournaments yet.</p>
                                        <a href="create_tournament.php" class="org-btn">
                                            <i class="fas fa-plus-circle"></i> Create your first tournament now!
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="org-pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="org-page-active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="org-page-link"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>