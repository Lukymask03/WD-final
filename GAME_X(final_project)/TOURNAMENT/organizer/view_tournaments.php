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
<title>Your Tournaments | Organizer | GameX</title>

<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* ====================== SIDEBAR OFFSET ====================== */
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f4f5f7;
    }

    /* ====================== MAIN CONTENT ====================== */
    main.content {
        margin-left: 280px;
        padding: 100px 40px 40px;
        min-height: 100vh;
    }

    .content h1 {
        color: #ff6600;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ====================== SEARCH FORM ====================== */
    .search-form {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .search-form input[type="text"] {
        flex: 1;
        min-width: 250px;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
    }

    .search-form input[type="text"]:focus {
        outline: none;
        border-color: #ff6600;
        box-shadow: 0 0 5px rgba(255, 102, 0, 0.3);
    }

    .search-form button,
    .search-form .btn {
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .search-form button {
        background: #ff6600;
        color: white;
    }

    .search-form button:hover {
        background: #e55a00;
    }

    .btn-primary {
        background: #28a745;
        color: white;
    }

    .btn-primary:hover {
        background: #218838;
    }

    /* ====================== TABLE ====================== */
    .table {
        width: 100%;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-collapse: collapse;
    }

    .table thead tr {
        background: #ff6600;
        color: white;
    }

    .table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }

    .table tbody tr:hover {
        background: #f9f9f9;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .no-data {
        text-align: center;
        color: #999;
        padding: 40px !important;
        font-style: italic;
    }

    /* ====================== ACTIONS ====================== */
    .actions {
        display: flex;
        gap: 10px;
    }

    .actions a {
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        color: white;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .actions .view {
        background: #007bff;
    }

    .actions .view:hover {
        background: #0056b3;
    }

    .actions .edit {
        background: #ffc107;
    }

    .actions .edit:hover {
        background: #e0a800;
    }

    .actions .delete {
        background: #dc3545;
    }

    .actions .delete:hover {
        background: #c82333;
    }

    /* ====================== PAGINATION ====================== */
    .pagination {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination .active-page {
        padding: 10px 16px;
        border: 1px solid #ddd;
        border-radius: 6px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
        font-weight: 600;
    }

    .pagination a:hover {
        background: #ff6600;
        color: white;
        border-color: #ff6600;
    }

    .pagination .active-page {
        background: #ff6600;
        color: white;
        border-color: #ff6600;
    }

    /* ====================== RESPONSIVE ====================== */
    @media (max-width: 768px) {
        main.content {
            margin-left: 0;
            padding: 90px 20px 40px;
        }

        .table {
            font-size: 12px;
        }

        .table th,
        .table td {
            padding: 10px 8px;
        }

        .search-form {
            flex-direction: column;
        }

        .search-form input[type="text"] {
            min-width: 100%;
        }
    }
</style>
</head>

<body>
<!-- Navigation -->
<?php include "../includes/organizer/organizer_header.php"; ?>
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
                        <td><?= date('M d, Y', strtotime($t['start_date'])) ?></td>
                        <td><?= $t['end_date'] ? date('M d, Y', strtotime($t['end_date'])) : 'N/A' ?></td>
                        <td>
                            <span style="
                                padding: 5px 12px;
                                border-radius: 20px;
                                font-size: 12px;
                                font-weight: 600;
                                background: <?= $t['status'] === 'open' ? '#d4edda' : ($t['status'] === 'completed' ? '#d1ecf1' : '#f8d7da') ?>;
                                color: <?= $t['status'] === 'open' ? '#155724' : ($t['status'] === 'completed' ? '#0c5460' : '#721c24') ?>;
                            ">
                                <?= strtoupper(htmlspecialchars($t['status'])) ?>
                            </span>
                        </td>

                        <td class="actions">
                            <a href="view_single_tournament.php?id=<?= $t['tournament_id'] ?>" class="view" title="View">
                                <i class="fa fa-eye"></i>
                            </a>

                            <a href="edit_tournament.php?id=<?= $t['tournament_id'] ?>" class="edit" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>

                            <a href="../backend/delete_tournament.php?id=<?= $t['tournament_id'] ?>"
                               onclick="return confirm('Are you sure you want to delete this tournament?');"
                               class="delete"
                               title="Delete">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr><td colspan="6" class="no-data">
                    <?php if (!empty($search)): ?>
                        No tournaments found matching "<?= htmlspecialchars($search) ?>".
                    <?php else: ?>
                        You haven't created any tournaments yet. <a href="create_tournament.php">Create one now!</a>
                    <?php endif; ?>
                </td></tr>
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