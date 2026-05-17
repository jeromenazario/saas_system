<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../config/db.php';
require '../config/log_activity.php';

$id        = intval($_GET['id'] ?? 0);
$client_id = intval($_GET['client_id'] ?? 0);

$stmt = $pdo->prepare("SELECT s.*, c.client_name FROM subscriptions s JOIN clients c ON c.id = s.client_id WHERE s.id=?");
$stmt->execute([$id]);
$sub = $stmt->fetch();
if (!$sub) { header('Location: ../index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_name  = trim($_POST['plan_name']);
    $price      = floatval($_POST['price']);
    $status     = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    if (empty($plan_name)) {
        $error = 'Plan name is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE subscriptions SET plan_name=?, price=?, status=?, start_date=?, end_date=?, updated_by=?, last_updated=NOW() WHERE id=?");
        $stmt->execute([$plan_name, $price, $status, $start_date ?: null, $end_date ?: null, $_SESSION['user_id'], $id]);

        // Log the activity — show old plan name → new plan name
        log_activity(
            $pdo,
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            'UPDATE',
            'subscription',
            $id,
            "Updated subscription \"{$sub['plan_name']}\" → \"{$plan_name}\" (\${$price}/mo, {$status}) for client \"{$sub['client_name']}\""
        );

        header("Location: ../clients/view.php?id={$client_id}&msg=Subscription+updated!");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Subscription – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>body{background:#f0f2f5;}.card{border:none;box-shadow:0 2px 12px rgba(0,0,0,.06);}</style>
</head>
<body>
<nav class="navbar navbar-dark px-4" style="background:#0d6efd">
  <a class="navbar-brand fw-bold" href="../index.php">⚡ SaaS Manager</a>
  <a href="../logout.php" class="btn btn-outline-light btn-sm ms-auto">Logout</a>
</nav>
<div class="container py-4" style="max-width:560px">
  <div class="card">
    <div class="card-body p-4">
      <h5 class="fw-bold mb-4"><i class="bi bi-pencil me-2 text-warning"></i>Edit Subscription</h5>
      <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
          <input type="text" name="plan_name" class="form-control" value="<?= htmlspecialchars($_POST['plan_name'] ?? $sub['plan_name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Price ($/month)</label>
          <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? $sub['price']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Status</label>
          <select name="status" class="form-select">
            <?php foreach (['trial','active','inactive'] as $opt): ?>
              <option value="<?= $opt ?>" <?= ($sub['status']==$opt)?'selected':'' ?>><?= ucfirst($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($_POST['start_date'] ?? $sub['start_date']) ?>">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($_POST['end_date'] ?? $sub['end_date']) ?>">
          </div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg me-1"></i>Update</button>
          <a href="../clients/view.php?id=<?= $client_id ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
