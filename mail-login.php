<?php
session_start();
require_once __DIR__ . '/mail-config.php';

// Already logged in → go to dashboard
if (!empty($_SESSION['mail_auth'])) {
    header('Location: pradip/mail/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Rate limit: max 5 attempts per 10 min
    if (!isset($_SESSION['attempts'])) $_SESSION['attempts'] = 0;
    if (!isset($_SESSION['attempt_time'])) $_SESSION['attempt_time'] = time();
    if (time() - $_SESSION['attempt_time'] > 600) {
        $_SESSION['attempts'] = 0;
        $_SESSION['attempt_time'] = time();
    }

    if ($_SESSION['attempts'] >= 5) {
        $error = 'Too many attempts. Please wait 10 minutes.';
    } elseif ($user === MAIL_USER && $pass === MAIL_PASS) {
        // Verify credentials actually work against IMAP
        $conn = @imap_open(
            '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}INBOX',
            MAIL_USER, MAIL_PASS
        );
        if ($conn) {
            imap_close($conn);
            session_regenerate_id(true);
            $_SESSION['mail_auth']    = true;
            $_SESSION['mail_user']    = $user;
            $_SESSION['login_time']   = time();
            $_SESSION['attempts']     = 0;
            header('Location: mail/index.php');
            exit;
        } else {
            $error = 'Mail server connection failed: ' . imap_last_error();
        }
    } else {
        $_SESSION['attempts']++;
        $remaining = 5 - $_SESSION['attempts'];
        $error = "Invalid credentials. $remaining attempt(s) remaining.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mail Login — Pradip Subedi</title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@600;700&family=Exo+2:wght@300;400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/pradip/assets/mail-login.css">
</head>
<body>
<div class="corner tl"></div><div class="corner tr"></div>
<div class="corner bl"></div><div class="corner br"></div>
<div class="line line1"></div><div class="line line2"></div>

<div class="card">
  <div class="logo">
    <div class="logo-icon"></div>
    <div class="logo-text">
      <h1>Mail Admin</h1>
      <small>pradipsubedi1.com.np</small>
    </div>
  </div>

  <div class="divider"></div>

  <div class="status-bar">
    <div class="status-dot"></div>
    <span class="status-text">SSL SECURED</span>
    <span class="status-host">mail.pradipsubedi1.com.np:993</span>
  </div>

  <?php if ($error): ?>
  <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= bin2hex(random_bytes(16)) ?>"/>
    <div class="field">
      <label>Email Address</label>
      <input type="email" name="username" placeholder="info@pradipsubedi1.com.np"
             value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus/>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••••••" required/>
    </div>
    <button type="submit" class="login-btn">⚡ Access Mail</button>
  </form>

  <div class="footer-note">ELECTRICAL ENGINEER · SECURE ACCESS</div>
  <div class="security-badges">
    <span class="s-badge">IMAP SSL</span>
    <span class="s-badge">SMTP TLS</span>
    <span class="s-badge">Session Auth</span>
  </div>
</div>
<?php include 'footer.php'; ?>