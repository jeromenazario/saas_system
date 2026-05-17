<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../config/db.php';

$client_id = intval($_GET['id'] ?? 0);
$cstmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$cstmt->execute([$client_id]);
$client = $cstmt->fetch();
if (!$client) { header('Location: ../index.php'); exit; }

$stmt = $pdo->prepare("
    SELECT s.*,
           u1.first_name AS added_first, u1.last_name AS added_last,
           u2.first_name AS updated_first, u2.last_name AS updated_last
    FROM subscriptions s
    LEFT JOIN users u1 ON s.added_by = u1.id
    LEFT JOIN users u2 ON s.updated_by = u2.id
    WHERE s.client_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$client_id]);
$subscriptions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subscriptions – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body{background:#f0f2f5;}
  .card{border:none;box-shadow:0 2px 12px rgba(0,0,0,.06);}
  .table th{background:#f8f9fa;font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;color:#6c757d;}
</style>
</head>
<body>
<nav class="navbar navbar-dark px-4" style="background:#0d6efd">
  <a class="navbar-brand fw-bold" href="../index.php">⚡ SaaS Manager</a>
  <span class="text-white opacity-75 ms-3 small"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
  <a href="../logout.php" class="btn btn-outline-light btn-sm ms-auto">Logout</a>
</nav>

<div class="container-fluid py-4 px-4">
  <div class="mb-3">
    <a href="../index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Clients</a>
  </div>

  <div class="card mb-3 p-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i><?= htmlspecialchars($client['client_name']) ?>
      <small class="text-muted fw-normal fs-6 ms-2"><?= htmlspecialchars($client['company'] ?? '') ?></small>
    </h5>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-success"></i>Subscriptions</h5>
        <a href="../subscriptions/add.php?client_id=<?= $client_id ?>" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Subscription</a>
      </div>

      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
      <?php endif; ?>

      <?php if (empty($subscriptions)): ?>
        <div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No subscriptions yet.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>#</th><th>Plan</th><th>Price</th><th>Status</th>
              <th>Start</th><th>End</th>
              <th>Added By</th><th>Last Updated</th><th>Updated By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($subscriptions as $i => $s): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><strong><?= htmlspecialchars($s['plan_name']) ?></strong></td>
              <td>$<?= number_format($s['price'], 2) ?></td>
              <td>
                <?php
                  $badges = ['active'=>'success','inactive'=>'danger','trial'=>'warning'];
                  $b = $badges[$s['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $b ?>"><?= ucfirst($s['status']) ?></span>
              </td>
              <td><small><?= $s['start_date'] ?? '—' ?></small></td>
              <td><small><?= $s['end_date'] ?? '—' ?></small></td>
              <td><span class="badge bg-info text-dark"><?= $s['added_first'] ? htmlspecialchars($s['added_first'].' '.$s['added_last']) : '—' ?></span></td>
              <td><small class="text-muted"><?= $s['last_updated'] ?></small></td>
              <td><span class="badge bg-secondary"><?= $s['updated_first'] ? htmlspecialchars($s['updated_first'].' '.$s['updated_last']) : '—' ?></span></td>
              <td>
                <a href="../subscriptions/edit.php?id=<?= $s['id'] ?>&client_id=<?= $client_id ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="../subscriptions/delete.php?id=<?= $s['id'] ?>&client_id=<?= $client_id ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this subscription?')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
