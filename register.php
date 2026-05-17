<?php
session_start();
require 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];
    $address    = trim($_POST['address']);
    $age        = intval($_POST['age']);
    $phone      = trim($_POST['phone']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, address, age, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $hashed, $address, $age, $phone]);
            $success = 'Account created! <a href="login.php">Login here</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #f0f2f5; }
  .card { border: none; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
  .card-header { background: #0d6efd; color: #fff; border-radius: 12px 12px 0 0 !important; }
  .brand-logo { font-size: 1.6rem; font-weight: 700; letter-spacing: -1px; }
</style>
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
      <div class="card rounded-3">
        <div class="card-header py-3 text-center">
          <div class="brand-logo">⚡ SaaS Manager</div>
          <p class="mb-0 mt-1 small opacity-75">Create your account</p>
        </div>
        <div class="card-body p-4">
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
          <?php endif; ?>
          <form method="POST" novalidate>
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Address</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Age</label>
                <input type="number" name="age" class="form-control" min="1" max="120" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              </div>
              <div class="col-12 d-grid mt-2">
                <button type="submit" class="btn btn-primary btn-lg">Register</button>
              </div>
            </div>
          </form>
          <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
