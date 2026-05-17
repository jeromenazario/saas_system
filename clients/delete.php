<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../config/db.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
$stmt->execute([$id]);

header('Location: ../index.php?msg=Client+deleted+successfully!');
exit;
?>
