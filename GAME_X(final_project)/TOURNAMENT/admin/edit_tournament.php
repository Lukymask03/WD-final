<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
require_once __DIR__ . '/../backend/db.php';

// Get tournament ID from URL
$tournament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tournament_id <= 0) {
    die("Invalid tournament ID");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title']);
        $game = trim($_POST['game']);
        $description = trim($_POST['description']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reg_start_date = $_POST['reg_start_date'];
        $reg_end_date = $_POST['reg_end_date'];
        $max_teams = intval($_POST['max_teams']);
        $status = $_POST['status'];
        
        $update_query = "UPDATE tournaments SET 
                        title = ?,
                        game = ?,
                        description = ?,
                        start_date = ?,
                        end_date = ?,
                        reg_start_date = ?,
                        reg_end_date = ?,
                        max_teams = ?,
                        status = ?
                        WHERE tournament_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->execute([
            $title,
            $game,
            $description,
            $start_date,
            $end_date,
            $reg_start_date,
            $reg_end_date,
            $max_teams,
            $status,
            $tournament_id
        ]);
        
        $_SESSION['success_message'] = "Tournament updated successfully!";
        header("Location: tournaments.php");
        exit();
        
    } catch (PDOException $e) {
        $error_message = "Error updating tournament: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch tournament details
try {
    $query = "SELECT * FROM tournaments WHERE tournament_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$tournament_id]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tournament) {
        die("Tournament not found");
    }
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tournament - <?php echo htmlspecialchars($tournament['title']); ?></title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #ff6600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #ff6600;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #ff6600;
            color: white;
        }

        .btn-primary:hover {
            background-color: #e55a00;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .required {
            color: #ff6600;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa fa-edit"></i> Edit Tournament</h1>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Tournament Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($tournament['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="game">Game <span class="required">*</span></label>
                <input type="text" id="game" name="game" value="<?php echo htmlspecialchars($tournament['game']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($tournament['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date <span class="required">*</span></label>
                    <input type="datetime-local" id="start_date" name="start_date" value="<?php echo date('Y-m-d\TH:i', strtotime($tournament['start_date'])); ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="datetime-local" id="end_date" name="end_date" value="<?php echo $tournament['end_date'] ? date('Y-m-d\TH:i', strtotime($tournament['end_date'])) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="reg_start_date">Registration Start</label>
                    <input type="datetime-local" id="reg_start_date" name="reg_start_date" value="<?php echo $tournament['reg_start_date'] ? date('Y-m-d\TH:i', strtotime($tournament['reg_start_date'])) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="reg_end_date">Registration End</label>
                    <input type="datetime-local" id="reg_end_date" name="reg_end_date" value="<?php echo $tournament['reg_end_date'] ? date('Y-m-d\TH:i', strtotime($tournament['reg_end_date'])) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="max_teams">Max Teams <span class="required">*</span></label>
                    <input type="number" id="max_teams" name="max_teams" value="<?php echo $tournament['max_teams']; ?>" min="2" required>
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="open" <?php echo $tournament['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="completed" <?php echo $tournament['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $tournament['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Update Tournament
                </button>
                <a href="tournaments.php" class="btn btn-secondary">
                    <i class="fa fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>