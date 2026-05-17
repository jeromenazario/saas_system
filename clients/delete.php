<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../config/db.php';
require '../config/log_activity.php';

$id = intval($_GET['id'] ?? 0);

// Fetch the client name BEFORE deleting so we can log it
$stmt = $pdo->prepare("SELECT client_name, company FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if ($client) {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);

    // Log the deletion
    log_activity(
        $pdo,
        $_SESSION['user_id'],
        $_SESSION['user_name'],
        'DELETE',
        'client',
        $id,
        "Deleted client: \"{$client['client_name']}\" (Company: {$client['company']})"
    );
}

header('Location: ../index.php?msg=Client+deleted+successfully!');
exit;
?>
