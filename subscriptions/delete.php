<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../config/db.php';

$id        = intval($_GET['id'] ?? 0);
$client_id = intval($_GET['client_id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM subscriptions WHERE id=?");
$stmt->execute([$id]);

header("Location: ../clients/view.php?id={$client_id}&msg=Subscription+deleted!");
exit;
?>
