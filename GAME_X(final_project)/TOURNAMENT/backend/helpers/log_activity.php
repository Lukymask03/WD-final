<?php
require_once __DIR__ . '/../db.php';  // PDO connection

/**
 * Logs user actions to the audit_logs table.
 *
 * @param int    $account_id  The user's account ID.
 * @param string $action      The action performed.
 * @param string $details     Optional details about the action (default: "N/A").
 * @return bool               True if log inserted successfully, false otherwise.
 */
function logActivity($account_id, $action, $details = "N/A") {
    global $conn;

    try {
        $stmt = $conn->prepare("
            INSERT INTO audit_logs (account_id, action, details, created_at)
            VALUES (:account_id, :action, :details, NOW())
        ");
        return $stmt->execute([
            ':account_id' => $account_id,
            ':action' => $action,
            ':details' => $details
        ]);
    } catch (PDOException $e) {
        error_log("Audit Log Error: " . $e->getMessage());
        return false;
    }
}
?>
