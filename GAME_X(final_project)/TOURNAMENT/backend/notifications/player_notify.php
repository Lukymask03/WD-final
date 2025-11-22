<?php
require __DIR__ . '/../db.php';

/**
 * Returns list of all active players who allowed notifications.
 */
function getAllPlayerEmails() {
    global $conn;

    try {
        $stmt = $conn->prepare("
            SELECT email 
            FROM accounts 
            WHERE role = 'player'
              AND account_status = 'active'
              AND notify_tournaments = 1
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);

    } catch (Exception $e) {
        error_log("Error in getAllPlayerEmails(): " . $e->getMessage());
        return [];
    }
}
