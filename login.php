<?php
/**
 * login.php – SaaS Manager
 * -----------------------------------------------------------------------
 * XSS SECURITY MEASURES APPLIED:
 *   1. sanitize_input() on all $_POST values
 *   2. htmlspecialchars() on all output echoed to the browser
 *   3. Content-Security-Policy header (no inline scripts allowed)
 *   4. HttpOnly + SameSite cookies (JS cannot read the session cookie)
 *   5. session_regenerate_id() after login to prevent session fixation
 * -----------------------------------------------------------------------
 */

// ── Block 3: CSP header + secure cookie settings (before any output) ────────
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net");
session_set_cookie_params([
    'httponly' => true,   // Cookie cannot be accessed by document.cookie
    'secure'   => false,  // Set to true on HTTPS
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
 * Trims whitespace, removes backslashes, HTML-encodes special characters.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Apply sanitize_input() to all POST fields ────────────────────────
    $email    = sanitize_input($_POST['email']);
    $password = $_POST['password']; // Don't htmlspecialchars the password itself

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // ── Prevent session fixation: regenerate ID after login ──────
            session_regenerate_id(true);

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – SaaS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #f0f2f5; }
  .card { border: none; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
  .card-header { background: #0d6efd; color: #fff; border-radius: 12px 12px 0 0 !important; }
  .brand-logo  { font-size: 1.6rem; font-weight: 700; letter-spacing: -1px; }
</style>
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="card rounded-3">
        <div class="card-header py-3 text-center">
          <div class="brand-logo">⚡ SaaS Manager</div>
          <p class="mb-0 mt-1 small opacity-75">Sign in to your account</p>
        </div>
        <div class="card-body p-4">

          <?php if ($error): ?>
            <!-- Block 2 (output): error already sanitized — encode anyway for safety -->
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>

          <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Registered successfully! Please log in.</div>
          <?php endif; ?>

          <?php if (isset($_GET['logged_out'])): ?>
            <div class="alert alert-info">You have been logged out.</div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Email Address</label>
              <!-- Block 2 (output): encode value echoed back into the input field -->
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                     required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid mt-3">
              <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
          </form>
          <p class="text-center mt-3 mb-0">No account yet? <a href="register.php">Register</a></p>
          <p class="text-center mt-2 mb-0">
            <small><a href="xss_vulnerable_demo.php" class="text-muted">🔬 XSS Demo</a></small>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
