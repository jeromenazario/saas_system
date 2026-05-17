<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require 'config/db.php';

$q       = trim($_GET['q'] ?? '');
$clients = [];
$subs    = [];

if ($q !== '') {
    $like = '%' . $q . '%';

    // Search clients (parent entity)
    $stmt = $pdo->prepare("
        SELECT c.*, u.first_name AS added_first, u.last_name AS added_last
        FROM clients c
        LEFT JOIN users u ON u.id = c.added_by
        WHERE c.client_name LIKE ? OR c.company LIKE ? OR c.industry LIKE ? OR c.email LIKE ?
        ORDER BY c.client_name
    ");
    $stmt->execute([$like, $like, $like, $like]);
    $clients = $stmt->fetchAll();

    // Search subscriptions (child entity)
    $stmt = $pdo->prepare("
        SELECT s.*, c.client_name, u.first_name AS added_first, u.last_name AS added_last
        FROM subscriptions s
        JOIN clients c ON c.id = s.client_id
        LEFT JOIN users u ON u.id = s.added_by
        WHERE s.plan_name LIKE ? OR s.status LIKE ? OR c.client_name LIKE ?
        ORDER BY c.client_name, s.plan_name
    ");
    $stmt->execute([$like, $like, $like]);
    $subs = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { background: #f0f2f5; }
  .navbar { background: #0d6efd !important; }
  .navbar-brand { font-weight: 700; font-size: 1.3rem; }
  .card { border: none; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
  .table th { background: #f8f9fa; font-size: .82rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
  .badge-active   { background:#d1fae5; color:#065f46; border-radius:20px; font-size:.75rem; padding:3px 10px; }
  .badge-inactive { background:#fee2e2; color:#991b1b; border-radius:20px; font-size:.75rem; padding:3px 10px; }
  .badge-trial    { background:#fef3c7; color:#92400e; border-radius:20px; font-size:.75rem; padding:3px 10px; }
  mark { background: #fef08a; border-radius: 3px; padding: 0 2px; }
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

  <!-- Search bar -->
  <div class="card mb-4">
    <div class="card-body py-3">
      <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control"
               placeholder="Search clients or subscriptions…"
               value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" autofocus>
        <button class="btn btn-primary px-4"><i class="bi bi-search me-1"></i>Search</button>
        <?php if ($q): ?>
          <a href="search.php" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <?php if ($q === ''): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-search fs-1 d-block mb-2"></i>Type something above to search clients and subscriptions.
    </div>

  <?php elseif (empty($clients) && empty($subs)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-emoji-frown fs-1 d-block mb-2"></i>
      No results found for <strong>"<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"</strong>
    </div>

  <?php else: ?>

    <!-- ── CLIENTS (Parent Entity) ──────────────────────────────────────────── -->
    <h5 class="fw-bold mb-3">
      <i class="bi bi-people me-2 text-primary"></i>Clients
      <span class="badge bg-primary ms-1"><?= count($clients) ?></span>
    </h5>

    <?php if (empty($clients)): ?>
      <p class="text-muted mb-4">No matching clients.</p>
    <?php else: ?>
    <div class="card mb-4">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Client Name</th><th>Company</th><th>Industry</th><th>Email</th><th>Phone</th><th>Added By</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
              <td><strong><?= htmlspecialchars($c['client_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
              <td><?= htmlspecialchars($c['company'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['industry'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(($c['added_first'] ?? '') . ' ' . ($c['added_last'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <a href="clients/view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                <a href="clients/edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="clients/delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                   onclick="return confirm('Delete this client?')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── SUBSCRIPTIONS (Child Entity) ────────────────────────────────────── -->
    <h5 class="fw-bold mb-3">
      <i class="bi bi-credit-card me-2 text-success"></i>Subscriptions
      <span class="badge bg-success ms-1"><?= count($subs) ?></span>
    </h5>

    <?php if (empty($subs)): ?>
      <p class="text-muted mb-4">No matching subscriptions.</p>
    <?php else: ?>
    <div class="card mb-4">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Client</th><th>Plan Name</th><th>Price</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Added By</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($subs as $s): ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['client_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
              <td><?= htmlspecialchars($s['plan_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>$<?= number_format($s['price'], 2) ?>/mo</td>
              <td><span class="badge-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
              <td><?= htmlspecialchars($s['start_date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($s['end_date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(($s['added_first'] ?? '') . ' ' . ($s['added_last'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <a href="subscriptions/edit.php?id=<?= $s['id'] ?>&client_id=<?= $s['client_id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="subscriptions/delete.php?id=<?= $s['id'] ?>&client_id=<?= $s['client_id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                   onclick="return confirm('Delete this subscription?')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  <?php endif; ?>

  <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
