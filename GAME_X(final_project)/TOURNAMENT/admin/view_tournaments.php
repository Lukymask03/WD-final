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

// Fetch tournament details
$query = "SELECT * FROM tournaments WHERE tournament_id = ?";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute([$tournament_id]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tournament) {
        die("Tournament not found");
    }
    
    // Try to fetch organizer info if user table exists (adjust table name as needed)
    $organizer_name = "N/A";
    $organizer_email = "N/A";
    
    try {
        // Try different possible user table names
        $user_query = "SELECT username, email FROM user WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->execute([$tournament['organizer_id']]);
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $organizer_name = $user_data['username'];
            $organizer_email = $user_data['email'];
        }
    } catch (PDOException $e) {
        // User table doesn't exist or different name - that's okay
    }
    
    // Fetch registered teams/participants count
    $registered_teams = 0;
    try {
        $team_query = "SELECT COUNT(*) as team_count FROM registrations WHERE tournament_id = ?";
        $team_stmt = $conn->prepare($team_query);
        $team_stmt->execute([$tournament_id]);
        $team_data = $team_stmt->fetch(PDO::FETCH_ASSOC);
        $registered_teams = $team_data['team_count'];
    } catch (PDOException $e) {
        // Registrations table doesn't exist - that's okay
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
    <title>View Tournament - <?php echo htmlspecialchars($tournament['title']); ?></title>
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
            max-width: 1000px;
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

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #ff6600;
        }

        .detail-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .detail-value {
            color: #666;
            font-size: 16px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .status-open {
            background-color: #4CAF50;
            color: white;
        }

        .status-completed {
            background-color: #2196F3;
            color: white;
        }

        .status-cancelled {
            background-color: #f44336;
            color: white;
        }

        .btn-back {
            display: inline-block;
            padding: 12px 30px;
            background-color: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #e55a00;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background-color: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;
        }

        .progress-fill {
            height: 100%;
            background-color: #ff6600;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.3s;
        }

        .section-title {
            font-size: 20px;
            color: #ff6600;
            margin: 30px 0 15px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏆 <?php echo htmlspecialchars($tournament['title']); ?></h1>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Tournament ID</div>
                <div class="detail-value">#<?php echo $tournament['tournament_id']; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Game</div>
                <div class="detail-value"><?php echo htmlspecialchars($tournament['game']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Organizer</div>
                <div class="detail-value"><?php echo htmlspecialchars($organizer_name); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Organizer Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($organizer_email); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Start Date</div>
                <div class="detail-value"><?php echo date('F j, Y - g:i A', strtotime($tournament['start_date'])); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">End Date</div>
                <div class="detail-value">
                    <?php 
                    echo $tournament['end_date'] 
                        ? date('F j, Y - g:i A', strtotime($tournament['end_date'])) 
                        : 'Not set'; 
                    ?>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Registration Start</div>
                <div class="detail-value">
                    <?php 
                    echo $tournament['reg_start_date'] 
                        ? date('F j, Y - g:i A', strtotime($tournament['reg_start_date'])) 
                        : 'Not set'; 
                    ?>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Registration End</div>
                <div class="detail-value">
                    <?php 
                    echo $tournament['reg_end_date'] 
                        ? date('F j, Y - g:i A', strtotime($tournament['reg_end_date'])) 
                        : 'Not set'; 
                    ?>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge status-<?php echo $tournament['status']; ?>">
                        <?php echo strtoupper($tournament['status']); ?>
                    </span>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Team Slots</div>
                <div class="detail-value">
                    <?php echo $registered_teams; ?> / <?php echo $tournament['max_teams']; ?> teams registered
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($registered_teams / $tournament['max_teams']) * 100; ?>%">
                            <?php echo round(($registered_teams / $tournament['max_teams']) * 100); ?>%
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($tournament['description']): ?>
            <div class="detail-item full-width">
                <div class="detail-label">Description</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($tournament['description'])); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px;">
            <a href="tournaments.php" class="btn-back">← Back to Tournaments</a>
        </div>
    </div>
</body>
</html>