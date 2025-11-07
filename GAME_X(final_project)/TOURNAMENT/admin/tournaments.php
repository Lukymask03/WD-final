<?php
// ========================================
// TOURNAMENTS MANAGEMENT PAGE (ADMIN)
// ========================================

// ✅ Start session and Auth Guard
require_once __DIR__ . "/../backend/helpers/auth_guard.php";
checkAuth('admin'); // ✅ restricts to admin only



// ✅ Database connection
require_once __DIR__ . "/../backend/db.php";

// ✅ Include layout (sidebar)
include __DIR__ . '/../includes/admin/sidebar.php';

// ----------------------------
// Pagination and Search Setup
// ----------------------------
$pageSize = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $pageSize;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // ✅ Count total tournaments
    if (!empty($search)) {
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM tournaments WHERE name LIKE :search");
        $countStmt->execute(['search' => "%$search%"]);
    } else {
        $countStmt = $conn->query("SELECT COUNT(*) FROM tournaments");
    }

    $totalTournaments = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalTournaments / $pageSize));

    // ✅ Fetch tournaments
    if (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT * FROM tournaments 
            WHERE name LIKE :search 
            ORDER BY start_date DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    } else {
        $stmt = $conn->prepare("
            SELECT * FROM tournaments 
            ORDER BY start_date DESC 
            LIMIT :limit OFFSET :offset
        ");
    }

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
    <title>Tournaments | Admin | GameX</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <!-- ✅ Sidebar -->
        <aside class="sidebar">
            <?php include __DIR__ . '/../../includes/admin/sidebar.php'; ?>
        </aside>

        <main class="content">
            <h1><i class="fa-solid fa-trophy"></i> Tournament Management</h1>

            <!-- 🔍 Search form -->
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="Search tournaments..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fa fa-search"></i> Search</button>
                <a href="../organizer/create_tournament.php" class="btn btn-primary"><i class="fa fa-plus"></i> Create Tournament</a>
            </form>

            <!-- 🏆 Tournament Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Game</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tournaments)): ?>
                        <?php foreach ($tournaments as $tournament): ?>
                            <tr>
                                <td><?= htmlspecialchars($tournament['name']) ?></td>
                                <td><?= htmlspecialchars($tournament['game_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($tournament['start_date']) ?></td>
                                <td><?= htmlspecialchars($tournament['end_date']) ?></td>
                                <td>
                                    <span class="status <?= strtolower($tournament['status']) ?>">
                                        <?= htmlspecialchars($tournament['status']) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="../organizer/view_tournaments.php?id=<?= urlencode($tournament['tournament_id']) ?>" class="view"><i class="fa fa-eye"></i></a>
                                    <a href="../organizer/edit_tournament.php?id=<?= urlencode($tournament['tournament_id']) ?>" class="edit"><i class="fa fa-edit"></i></a>
                                    <a href="../../includes/organizer/delete_tournament.php?id=<?= urlencode($tournament['tournament_id']) ?>"
                                       onclick="return confirm('Are you sure you want to delete this tournament?');"
                                       class="delete"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="no-data">No tournaments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- 📄 Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($page == $i): ?>
                            <span class="active-page"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; <?= date("Y"); ?> Game X Community Admin Panel. All rights reserved.</p>
    </footer>
</body>
</html>
