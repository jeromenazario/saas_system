<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../config/db.php';
require '../config/log_activity.php';

$id        = intval($_GET['id'] ?? 0);
$client_id = intval($_GET['client_id'] ?? 0);

// Fetch the subscription BEFORE deleting so we can log it
$stmt = $pdo->prepare("SELECT s.*, c.client_name FROM subscriptions s JOIN clients c ON c.id = s.client_id WHERE s.id = ?");
$stmt->execute([$id]);
$sub = $stmt->fetch();

if ($sub) {
    $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE id = ?");
    $stmt->execute([$id]);

    // Log the deletion
    log_activity(
        $pdo,
        $_SESSION['user_id'],
        $_SESSION['user_name'],
        'DELETE',
        'subscription',
        $id,
        "Deleted subscription \"{$sub['plan_name']}\" from client \"{$sub['client_name']}\""
    );
}

header("Location: ../clients/view.php?id={$client_id}&msg=Subscription+deleted!");
exit;
?>
