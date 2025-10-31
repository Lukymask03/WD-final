<?php
require_once "db.php";
session_start();

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$tournament_id = $_GET['id'];

try {
    $stmt = $conn->prepare("DELETE FROM tournaments WHERE tournament_id = ?");
    $stmt->execute([$tournament_id]);
    header("Location: ../organizer/view_tournaments.php?msg=deleted");
    exit;
} catch (PDOException $e) {
    die("Error deleting tournament: " . htmlspecialchars($e->getMessage()));
}
