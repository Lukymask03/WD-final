<?php 
// ========================================
// ORGANIZER TOURNAMENT MANAGEMENT
// ========================================

session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/auth_guard.php';

checkAuth('organizer');

// Organizer ID from session
$organizer_id = $_SESSION['account_id'];

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

    // ⭐ Count tournaments created by this organizer
    if (!empty($search)) {
        $countStmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM tournaments 
            WHERE organizer_id = :oid AND title LIKE :search
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

    // ⭐ Fetch tournaments created by organizer
    if (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT * FROM tournaments
            WHERE organizer_id = :oid AND title LIKE :search
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
<title>Your Tournaments | Organizer | GameX</title>

<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    main.content {
        margin-left: 260px;
        padding: 25px;
    }

    .content h1 {
        color: #ff6600;
    }

    .table th {
        background: #ff6600;
        color: #fff;
    }
</style>
</head>

<body>
<!-- Navigation -->
<?php include "../includes/organizer/organizer_header.php"; ?>
<?php include "../includes/organizer/organizer_sidebar.php"; ?>


<!-- Organizer Sidebar -->
<?php include $sidebarPath; ?>

<main class="content">

    <h1><i class="fa-solid fa-trophy"></i> My Tournaments</h1>

    <!-- Search + Create -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search tournaments..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fa fa-search"></i> Search</button>
        <a href="create_tournament.php" class="btn btn-primary">
            <i class="fa fa-plus"></i> Create Tournament
        </a>
    </form>

    <!-- Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Game</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($tournaments): ?>
                <?php foreach ($tournaments as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['title']) ?></td>
                        <td><?= htmlspecialchars($t['game']) ?></td>
                        <td><?= htmlspecialchars($t['start_date']) ?></td>
                        <td><?= htmlspecialchars($t['end_date']) ?></td>
                        <td><?= htmlspecialchars($t['status']) ?></td>

                        <td class="actions">
                            <a href="view_single_tournament.php?id=<?= $t['tournament_id'] ?>" class="view">
                                <i class="fa fa-eye"></i>
                            </a>

                            <a href="edit_tournament.php?id=<?= $t['tournament_id'] ?>" class="edit">
                                <i class="fa fa-edit"></i>
                            </a>

                            <a href="../backend/delete_tournament.php?id=<?= $t['tournament_id'] ?>"
                               onclick="return confirm('Delete this tournament?');"
                               class="delete">
                                <i class="fa fa-trash"></i>
                            </a>
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
