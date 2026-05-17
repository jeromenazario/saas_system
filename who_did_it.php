<?php

// ── CSP + secure session (same as login/register) ───────────────────────────
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net https://cdn.jsdelivr.net");
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Strict']);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config/db.php';

// ── Fetch all clients with the user who added them and last updated them ─────
$stmt = $pdo->query("
    SELECT
        c.id,
        c.client_name,
        c.company,
        c.created_at,
        c.last_updated,
        u1.first_name AS added_first,
        u1.last_name  AS added_last,
        u1.email      AS added_email,
        u2.first_name AS updated_first,
        u2.last_name  AS updated_last,
        u2.email      AS updated_email
    FROM clients c
    LEFT JOIN users u1 ON c.added_by   = u1.id
    LEFT JOIN users u2 ON c.updated_by = u2.id
    ORDER BY c.last_updated DESC
");
$records = $stmt->fetchAll();

// ── Summary: count actions per user ─────────────────────────────────────────
$stmt_summary = $pdo->query("
    SELECT
        u.first_name,
        u.last_name,
        u.email,
        COUNT(DISTINCT c_add.id)  AS clients_added,
        COUNT(DISTINCT c_upd.id)  AS clients_updated
    FROM users u
    LEFT JOIN clients c_add ON c_add.added_by   = u.id
    LEFT JOIN clients c_upd ON c_upd.updated_by = u.id
    GROUP BY u.id
    ORDER BY u.first_name
");
$summary = $stmt_summary->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Who Did It – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body  { background: #f0f2f5; }
  .navbar  { background: #0d6efd !important; }
  .navbar-brand { font-weight: 700; font-size: 1.3rem; }
  .card { border: none; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
  .table th { background: #f8f9fa; font-size: .82rem; text-transform: uppercase;
              letter-spacing: .5px; color: #6c757d; }
  .badge-user { background: #dbeafe; color: #1e40af; border-radius: 20px;
                font-size: .78rem; padding: 3px 10px; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark navbar-expand-lg px-4">
  <a class="navbar-brand" href="index.php">⚡ SaaS Manager</a>
  <div class="ms-auto d-flex align-items-center gap-3">
    <span class="text-white opacity-75 small">
      <i class="bi bi-person-circle me-1"></i>
      <!-- Block 2 (output): encode session value before echoing -->
      <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?>
    </span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">
      <i class="bi bi-box-arrow-right me-1"></i>Logout
    </a>
  </div>
</nav>

<div class="container-fluid py-4 px-4">

  <h4 class="fw-bold mb-4"><i class="bi bi-person-check me-2 text-primary"></i>Who Did It – Audit Trail</h4>

  <!-- ── Summary cards ──────────────────────────────────────────────────── -->
  <div class="row g-3 mb-4">
    <?php foreach ($summary as $s): ?>
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card p-3">
        <div class="fw-semibold mb-1">
          <!-- Block 2 (output): always encode user data from DB -->
          <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="text-muted small mb-2">
          <?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="d-flex gap-3">
          <span class="text-success small">
            <i class="bi bi-plus-circle me-1"></i>
            <strong><?= intval($s['clients_added']) ?></strong> added
          </span>
          <span class="text-warning small">
            <i class="bi bi-pencil me-1"></i>
            <strong><?= intval($s['clients_updated']) ?></strong> updated
          </span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Full audit table ───────────────────────────────────────────────── -->
  <div class="card">
    <div class="card-body">
      <h5 class="fw-bold mb-3">
        <i class="bi bi-table me-2 text-primary"></i>Full Client Activity Log
      </h5>

      <?php if (empty($records)): ?>
        <div class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>No activity yet.
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Client Name</th>
              <th>Company</th>
              <th>Added By</th>
              <th>Date Added</th>
              <th>Last Updated By</th>
              <th>Last Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($records as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><strong><?= htmlspecialchars($r['client_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
              <td><?= htmlspecialchars($r['company'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>

              <!-- Added By -->
              <td>
                <?php if ($r['added_first']): ?>
                  <span class="badge-user">
                    <?= htmlspecialchars($r['added_first'] . ' ' . $r['added_last'], ENT_QUOTES, 'UTF-8') ?>
                  </span><br>
                  <small class="text-muted"><?= htmlspecialchars($r['added_email'], ENT_QUOTES, 'UTF-8') ?></small>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>

              <td><small class="text-muted"><?= htmlspecialchars($r['created_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></small></td>

              <!-- Last Updated By -->
              <td>
                <?php if ($r['updated_first']): ?>
                  <span class="badge-user">
                    <?= htmlspecialchars($r['updated_first'] . ' ' . $r['updated_last'], ENT_QUOTES, 'UTF-8') ?>
                  </span><br>
                  <small class="text-muted"><?= htmlspecialchars($r['updated_email'], ENT_QUOTES, 'UTF-8') ?></small>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>

              <td><small class="text-muted"><?= htmlspecialchars($r['last_updated'] ?? '—', ENT_QUOTES, 'UTF-8') ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <div class="mt-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </a>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
