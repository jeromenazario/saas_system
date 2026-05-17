<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../config/db.php';
require '../config/log_activity.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = trim($_POST['client_name']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone']);
    $company     = trim($_POST['company']);
    $industry    = trim($_POST['industry']);

    if (empty($client_name)) {
        $error = 'Client name is required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO clients (client_name, email, phone, company, industry, added_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$client_name, $email, $phone, $company, $industry, $_SESSION['user_id'], $_SESSION['user_id']]);
        $new_id = $pdo->lastInsertId();

        // Log the activity
        log_activity(
            $pdo,
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            'CREATE',
            'client',
            $new_id,
            "Added new client: \"{$client_name}\" (Company: {$company})"
        );

        header('Location: ../index.php?msg=Client+added+successfully!');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Client – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>body{background:#f0f2f5;}.card{border:none;box-shadow:0 2px 12px rgba(0,0,0,.06);}</style>
</head>
<body>
<nav class="navbar navbar-dark px-4" style="background:#0d6efd">
  <a class="navbar-brand fw-bold" href="../index.php">⚡ SaaS Manager</a>
  <a href="../logout.php" class="btn btn-outline-light btn-sm ms-auto">Logout</a>
</nav>
<div class="container py-4" style="max-width:600px">
  <div class="card">
    <div class="card-body p-4">
      <h5 class="fw-bold mb-4"><i class="bi bi-person-plus me-2 text-primary"></i>Add New Client</h5>
      <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-semibold">Client Name <span class="text-danger">*</span></label>
          <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($_POST['client_name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Company</label>
          <input type="text" name="company" class="form-control" value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Industry</label>
          <input type="text" name="industry" class="form-control" value="<?= htmlspecialchars($_POST['industry'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Client</button>
          <a href="../index.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
