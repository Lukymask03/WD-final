<?php
// Load database
require_once __DIR__ . '/../db.php';

// Load email systems (optional)
require_once __DIR__ . '/email_templates.php';
require_once __DIR__ . '/player_notify.php';
require_once __DIR__ . '/sendEmail.php';

/**
 * Insert notification into database
 */
function createNotificationDB($account_id, $title, $message, $type = 'system') {
    global $conn;

    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (account_id, title, message, type)
            VALUES (:account_id, :title, :message, :type)
        ");

        return $stmt->execute([
            ':account_id' => $account_id,
            ':title'      => $title,
            ':message'    => $message,
            ':type'       => $type
        ]);

    } catch (PDOException $e) {
        error_log("Notification DB Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log admin announcements
 */
function logAnnouncement($admin_id, $type, $title, $content) {
    global $conn;

    try {
        $stmt = $conn->prepare("
            INSERT INTO announcement_logs (admin_id, type, title, content)
            VALUES (:admin_id, :type, :title, :content)
        ");

        return $stmt->execute([
            ':admin_id' => $admin_id,
            ':type'     => $type,
            ':title'    => $title,
            ':content'  => $content
        ]);

    } catch (PDOException $e) {
        error_log("Announcement Log Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Optional email sender (disabled)
 */
function sendEmailOptional($email, $subject, $body) {
    if (!function_exists("sendEmail")) return;
    // sendEmail($email, $subject, $body);   // DISABLED
}

/**
 * MAIN NOTIFICATION ENGINE
 */
function triggerNotification($event, $data = []) {
    global $conn;

    // Get admin_id (for announcement logs)
    $admin_id = $_SESSION['account_id'] ?? null;

    switch ($event) {

        // SYSTEM UPDATE ANNOUNCEMENT
        case "system_update":

            // Notify ALL players
            $players = $conn->query("SELECT account_id FROM accounts WHERE role='player'");
            foreach ($players as $p) {
                createNotificationDB(
                    $p['account_id'],
                    $data['title'],
                    $data['content'],
                    "system"
                );
            }

            // Save announcement to logs
            if ($admin_id) {
                logAnnouncement($admin_id, "system_update", $data['title'], $data['content']);
            }

            break;

        // TOURNAMENT ANNOUNCEMENT
        case "tournament_created":

            // Notify ALL players
            $players = $conn->query("SELECT account_id FROM accounts WHERE role='player'");
            foreach ($players as $p) {
                createNotificationDB(
                    $p['account_id'],
                    "New Tournament: " . $data['title'],
                    $data['description'],
                    "tournament"
                );
            }

            // Save announcement to logs
            if ($admin_id) {
                logAnnouncement($admin_id, "tournament_created", $data['title'], $data['description']);
            }

            break;
    }
}
?>
