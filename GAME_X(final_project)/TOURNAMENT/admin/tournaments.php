<?php
// ========================================
// ADMIN TOURNAMENT MANAGEMENT
// ========================================

// ✅ Start session and restrict access
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// ✅ Database connection
require_once __DIR__ . '/../backend/db.php';

// ✅ Sidebar include path
$sidebarPath = __DIR__ . '/../includes/admin/sidebar.php';
if (!file_exists($sidebarPath)) {
    die("Sidebar file not found at: " . htmlspecialchars($sidebarPath));
}

// ----------------------------
// Pagination & Search Setup
// ----------------------------
$pageSize = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $pageSize;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Count total tournaments
    if (!empty($search)) {
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM tournaments WHERE name LIKE :search");
        $countStmt->execute(['search' => "%$search%"]);
    } else {
        $countStmt = $conn->query("SELECT COUNT(*) FROM tournaments");
    }
    $totalTournaments = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalTournaments / $pageSize));

    // Fetch tournaments with pagination
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
<link rel="stylesheet" href="../assets/css/admin_sidebar.css">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Main Content Offset */
    main.content {
        margin-left: 270px; /* match sidebar width */
        padding: 30px;
    }

    .content h1 { margin-bottom: 20px; color: #ff6600; }
    .search-form { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .search-form input { flex: 1; padding: 5px 10px; border-radius: 5px; border: 1px solid #ccc; }
    .search-form button, .search-form .btn-primary { padding: 5px 10px; border-radius: 5px; cursor: pointer; text-decoration: none; }
    .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .table th { background: #f5f6fa; }
    .actions a { margin-right: 5px; }
    .pagination { display: flex; gap: 5px; flex-wrap: wrap; }
    .pagination a, .pagination span { padding: 5px 10px; border: 1px solid #ddd; border-radius: 5px; text-decoration: none; color: #333; }
    .pagination .active-page { background: #ff6600; color: #fff; border-color: #ff6600; }
    .no-data { text-align: center; padding: 15px; }

    /* Responsive adjustments */
    @media screen and (max-width: 768px) {
        main.content { margin-left: 0; padding: 15px; }
        .search-form { flex-direction: column; }
    }
</style>
</head>
<body>

<!-- ✅ Sidebar -->
<?php include $sidebarPath; ?>

<!-- Main Content -->
<main class="content">
    <h1><i class="fa-solid fa-trophy"></i> Tournament Management</h1>

    <!-- Search + Create -->
    <form method="GET" action="" class="search-form">
        <input type="text" name="search" placeholder="Search tournaments..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fa fa-search"></i> Search</button>
        <a href="create_tournament.php" class="btn btn-primary"><i class="fa fa-plus"></i> Create Tournament</a>
    </form>

    <!-- Tournament Table -->
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
            <?php if ($tournaments): ?>
                <?php foreach ($tournaments as $tournament): ?>
                    <tr>
                        <td><?= htmlspecialchars($tournament['name']) ?></td>
                        <td><?= htmlspecialchars($tournament['game_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($tournament['start_date']) ?></td>
                        <td><?= htmlspecialchars($tournament['end_date']) ?></td>
                        <td><span class="status <?= strtolower($tournament['status']) ?>"><?= htmlspecialchars($tournament['status']) ?></span></td>
                        <td class="actions">
                            <a href="view_tournaments.php?id=<?= urlencode($tournament['tournament_id']) ?>" class="view"><i class="fa fa-eye"></i></a>
                            <a href="edit_tournament.php?id=<?= urlencode($tournament['tournament_id']) ?>" class="edit"><i class="fa fa-edit"></i></a>
                            <a href="delete_tournament.php?id=<?= urlencode($tournament['tournament_id']) ?>" onclick="return confirm('Are you sure you want to delete this tournament?');" class="delete"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="no-data">No tournaments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active-page"><?= $i ?></span>
                <?php else: ?>
                    <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
