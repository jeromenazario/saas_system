<?php
session_start();
// REDIRECT TO LOGIN IF NOT LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'config/db.php';

// Fetch all clients with added_by and last_updated info
$stmt = $pdo->query("
    SELECT c.*, 
           u1.first_name AS added_first, u1.last_name AS added_last,
           u2.first_name AS updated_first, u2.last_name AS updated_last
    FROM clients c
    LEFT JOIN users u1 ON c.added_by = u1.id
    LEFT JOIN users u2 ON c.updated_by = u2.id
    ORDER BY c.created_at DESC
");
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { background: #f0f2f5; }
  .navbar { background: #0d6efd !important; }
  .navbar-brand { font-weight: 700; font-size: 1.3rem; letter-spacing: -0.5px; }
  .card { border: none; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
  .table th { background: #f8f9fa; font-size: .82rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
  .badge-active   { background: #d1fae5; color: #065f46; }
  .badge-inactive { background: #fee2e2; color: #991b1b; }
  .badge-trial    { background: #fef3c7; color: #92400e; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark navbar-expand-lg px-4">
  <a class="navbar-brand" href="index.php">⚡ SaaS Manager</a>
  <div class="ms-auto d-flex align-items-center gap-3">
    <a href="search.php" class="btn btn-outline-light btn-sm"><i class="bi bi-search me-1"></i>Search</a>
    <a href="activity_logs.php" class="btn btn-outline-light btn-sm"><i class="bi bi-journal-text me-1"></i>Activity Logs</a>
    <a href="who_did_it.php" class="btn btn-outline-light btn-sm"><i class="bi bi-person-check me-1"></i>Who Did It</a>
    <span class="text-white opacity-75 small"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name']) ?></span>
    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
  </div>
</nav>

<div class="container-fluid py-4 px-4">

  <!-- Stats Row -->
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card p-3 text-center">
        <div class="fs-2 fw-bold text-primary"><?= count($clients) ?></div>
        <div class="text-muted small">Total Clients</div>
      </div>
    </div>
    <div class="col-sm-4">
      <?php
        $subCount = $pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
      ?>
      <div class="card p-3 text-center">
        <div class="fs-2 fw-bold text-success"><?= $subCount ?></div>
        <div class="text-muted small">Total Subscriptions</div>
      </div>
    </div>
    <div class="col-sm-4">
      <?php
        $activeCount = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();
      ?>
      <div class="card p-3 text-center">
        <div class="fs-2 fw-bold text-warning"><?= $activeCount ?></div>
        <div class="text-muted small">Active Subscriptions</div>
      </div>
    </div>
  </div>

  <!-- Clients Table -->
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Clients</h5>
        <a href="clients/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Client</a>
      </div>

      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_GET['msg']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (empty($clients)): ?>
        <div class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>No clients yet. <a href="clients/add.php">Add one!</a>
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Client Name</th>
              <th>Company</th>
              <th>Industry</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Added By</th>
              <th>Last Updated</th>
              <th>Updated By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $i => $c): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><strong><?= htmlspecialchars($c['client_name']) ?></strong></td>
              <td><?= htmlspecialchars($c['company'] ?? '—') ?></td>
              <td><?= htmlspecialchars($c['industry'] ?? '—') ?></td>
              <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
              <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
              <td>
                <span class="badge bg-info text-dark">
                  <?= $c['added_first'] ? htmlspecialchars($c['added_first'] . ' ' . $c['added_last']) : '—' ?>
                </span>
              </td>
              <td><small class="text-muted"><?= $c['last_updated'] ?></small></td>
              <td>
                <span class="badge bg-secondary">
                  <?= $c['updated_first'] ? htmlspecialchars($c['updated_first'] . ' ' . $c['updated_last']) : '—' ?>
                </span>
              </td>
              <td>
                <a href="clients/view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info" title="Subscriptions"><i class="bi bi-eye"></i></a>
                <a href="clients/edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="clients/delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this client and all their subscriptions?')"><i class="bi bi-trash"></i></a>
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
