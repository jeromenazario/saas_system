<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require 'config/db.php';

// Fetch all logs, newest first — no edit or delete allowed on this page
$logs = $pdo->query("
    SELECT * FROM activity_logs
    ORDER BY created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activity Logs – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body  { background: #f0f2f5; }
  .navbar { background: #0d6efd !important; }
  .navbar-brand { font-weight: 700; font-size: 1.3rem; }
  .card { border: none; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
  .table th { background: #f8f9fa; font-size: .82rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
  .badge-create { background: #d1fae5; color: #065f46; }
  .badge-update { background: #fef3c7; color: #92400e; }
  .badge-delete { background: #fee2e2; color: #991b1b; }
  .badge-action { border-radius: 20px; font-size: .75rem; padding: 3px 10px; font-weight: 600; }
  .badge-entity { background: #dbeafe; color: #1e40af; border-radius: 20px; font-size: .75rem; padding: 3px 10px; }
</style>
</head>
<body>

<nav class="navbar navbar-dark navbar-expand-lg px-4">
  <a class="navbar-brand" href="index.php">⚡ SaaS Manager</a>
  <div class="ms-auto d-flex align-items-center gap-3">
    <span class="text-white opacity-75 small">
      <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?>
    </span>
    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
  </div>
</nav>

<div class="container-fluid py-4 px-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Activity Logs</h4>
    <span class="text-muted small">Read-only — <?= count($logs) ?> records</span>
  </div>

  <div class="card">
    <div class="card-body">
      <?php if (empty($logs)): ?>
        <div class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>No activity recorded yet. Start adding clients and subscriptions!
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Date & Time</th>
              <th>User</th>
              <th>Action</th>
              <th>Entity</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $i => $log): ?>
            <tr>
              <td class="text-muted small"><?= $log['id'] ?></td>
              <td><small class="text-muted"><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
              <td>
                <span class="fw-semibold"><?= htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td>
                <?php
                  $badge = match($log['action']) {
                      'CREATE' => 'badge-create',
                      'UPDATE' => 'badge-update',
                      'DELETE' => 'badge-delete',
                      default  => ''
                  };
                ?>
                <span class="badge-action <?= $badge ?>"><?= $log['action'] ?></span>
              </td>
              <td>
                <span class="badge-entity"><?= ucfirst(htmlspecialchars($log['entity_type'], ENT_QUOTES, 'UTF-8')) ?></span>
              </td>
              <td><?= htmlspecialchars($log['description'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="mt-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
