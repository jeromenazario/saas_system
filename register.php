<?php
/**
 * register.php – SaaS Manager
 * -----------------------------------------------------------------------
 * XSS SECURITY MEASURES APPLIED:
 *   1. sanitize_input() trims + htmlspecialchars every $_POST value
 *   2. All output uses htmlspecialchars() before echo
 *   3. Content-Security-Policy header blocks inline script injection
 *   4. Passwords validated for: 8+ chars, uppercase, lowercase, number
 * -----------------------------------------------------------------------
 */

// ── Block 3: CSP + secure session cookies (send BEFORE any output) ──────────
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net");
session_set_cookie_params([
    'httponly' => true,   // Cookie cannot be read by document.cookie
    'secure'   => false,  // Set to true when using HTTPS
    'samesite' => 'Strict'
]);
session_start();
require 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// ── Block 1: Sanitize input function ────────────────────────────────────────
/**
 * Sanitizes user input to prevent XSS.
 * Trims whitespace, removes backslashes, and HTML-encodes special characters.
 * Call on every value from $_POST / $_GET / $_COOKIE.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// ── Block 2: Password validation function ───────────────────────────────────
function validate_password($password) {
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain at least one number (0-9).';
    }
    return '';
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Apply sanitize_input() to every field ────────────────────────────
    $first_name = sanitize_input($_POST['first_name']);
    $last_name  = sanitize_input($_POST['last_name']);
    $email      = sanitize_input($_POST['email']);
    $address    = sanitize_input($_POST['address'] ?? '');
    $age        = intval($_POST['age'] ?? 0);
    $phone      = sanitize_input($_POST['phone'] ?? '');

    // Password is validated BEFORE sanitizing (sanitizing alters special chars)
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // ── Block 2 in action: validate password strength ────────────────
        $pw_error = validate_password($password);
        if ($pw_error !== '') {
            $error = $pw_error;
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'That email is already registered.';
            } else {
                // Hash password with bcrypt before storing (never store plain text)
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt   = $pdo->prepare(
                    "INSERT INTO users (first_name, last_name, email, password, address, age, phone)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$first_name, $last_name, $email, $hashed, $address, $age, $phone]);
                $success = 'Account created! <a href="login.php">Login here</a>.';
            }
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
  .brand-logo  { font-size: 1.6rem; font-weight: 700; letter-spacing: -1px; }
  .pw-rule     { font-size: .8rem; color: #6c757d; margin-top: 4px; }
  .pw-rule li  { list-style: none; }
  .pw-rule li::before { content: '○ '; }
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
            <!-- Block 2 (output): error message is already sanitized via sanitize_input() -->
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                <!-- Block 2 (output): re-encode when echoing back to HTML -->
                <input type="text" name="first_name" class="form-control"
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control"
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required>
                <!-- Password policy hint for the user -->
                <ul class="pw-rule">
                  <li>Minimum 8 characters</li>
                  <li>At least one uppercase letter</li>
                  <li>At least one lowercase letter</li>
                  <li>At least one number</li>
                </ul>
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Address</label>
                <input type="text" name="address" class="form-control"
                       value="<?= htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Age</label>
                <input type="number" name="age" class="form-control" min="1" max="120"
                       value="<?= htmlspecialchars($_POST['age'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="col-sm-6">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
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
