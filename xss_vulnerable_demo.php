<?php
session_start();
require 'config/db.php';

$search  = $_GET['q'] ?? '';
$secured = isset($_GET['secured']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $secured ? '✅ Secured' : '⚠️ Vulnerable' ?> – XSS Demo | SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #f0f2f5; }
  .demo-badge { font-size: .75rem; padding: 3px 10px; border-radius: 20px; }
  .vuln  { background:#fee2e2; color:#991b1b; }
  .safe  { background:#d1fae5; color:#065f46; }
.card  { border:none; box-shadow:0 2px 12px rgba(0,0,0,.07); }
</style>
</head>
<body>
<div class="container py-5" style="max-width:750px">

  <!-- header -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <span class="demo-badge fw-bold <?= $secured ? 'safe' : 'vuln' ?>">
      <?= $secured ? '✅ SECURED VERSION' : '⚠️ VULNERABLE VERSION' ?>
    </span>
    <h4 class="mb-0">XSS Demo – SaaS Manager</h4>
  </div>

  <!-- search form -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET">
        <?php if ($secured): ?>
          <input type="hidden" name="secured" value="1">
        <?php endif; ?>
        <label class="form-label fw-semibold">Search client name:</label>
        <div class="input-group">
          <input type="text" name="q" class="form-control"
                 value="<?= $secured ? htmlspecialchars($search, ENT_QUOTES, 'UTF-8') : $search ?>"
                 placeholder="Search client name…">
          <button class="btn btn-primary" type="submit">Search</button>
        </div>
      </form>
    </div>
  </div>

  <!-- result / output -->
  <?php if ($search !== ''): ?>
  <div class="card mb-4">
    <div class="card-body">
      <p class="mb-1 text-muted small">Search result:</p>
      <p class="mb-0">
        You searched for: <strong>
        <?php
          if ($secured) {
              echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
          } else {
              echo $search;
          }
        ?>
        </strong>
      </p>
    </div>
  </div>
  <?php endif; ?>

  <!-- toggle link -->
  <div class="text-center">
    <?php if (!$secured): ?>
      <a href="xss_vulnerable_demo.php?secured=1&q=<?= urlencode($search) ?>"
         class="btn btn-success">✅ View Secured Version</a>
    <?php else: ?>
      <a href="xss_vulnerable_demo.php?q=<?= urlencode($search) ?>"
         class="btn btn-danger">⚠️ View Vulnerable Version</a>
    <?php endif; ?>
    <a href="index.php" class="btn btn-outline-secondary ms-2">← Back to Dashboard</a>
  </div>

</div>
</body>
</html>
