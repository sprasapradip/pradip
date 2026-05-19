<?php
session_start();
require_once __DIR__ . '/mail-config.php';

// Already logged in → go to dashboard
if (!empty($_SESSION['mail_auth'])) {
    header('Location: mail/index.php');
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
  <style>
    :root {
      --bg:     #060a10;
      --panel:  #0b1422;
      --border: #1a3354;
      --accent: #00c8f0;
      --accent2:#ff9500;
      --green:  #00ff88;
      --red:    #ff4757;
      --text:   #c0d8ee;
      --muted:  #3a5a7a;
      --glow:   0 0 20px rgba(0,200,240,.35);
      --glow2:  0 0 40px rgba(0,200,240,.6);
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }

    body {
      min-height: 100vh;
      background: var(--bg);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Exo 2', sans-serif;
      overflow: hidden;
      background-image:
        radial-gradient(ellipse at 30% 40%, rgba(0,100,200,.1) 0%, transparent 55%),
        radial-gradient(ellipse at 75% 70%, rgba(0,200,240,.06) 0%, transparent 50%),
        repeating-linear-gradient(0deg, transparent, transparent 49px, rgba(26,51,84,.2) 50px),
        repeating-linear-gradient(90deg, transparent, transparent 49px, rgba(26,51,84,.2) 50px);
    }

    /* Scanlines */
    body::before {
      content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
      background: repeating-linear-gradient(0deg,transparent 0,transparent 2px,rgba(0,0,0,.06) 2px,rgba(0,0,0,.06) 4px);
    }

    /* Corner decorations */
    .corner { position:fixed; width:28px; height:28px; border-color:var(--accent); border-style:solid; opacity:.3; }
    .tl { top:14px; left:14px; border-width:2px 0 0 2px; }
    .tr { top:14px; right:14px; border-width:2px 2px 0 0; }
    .bl { bottom:14px; left:14px; border-width:0 0 2px 2px; }
    .br { bottom:14px; right:14px; border-width:0 2px 2px 0; }

    /* Animated lines */
    .line { position:fixed; background:linear-gradient(90deg,transparent,var(--accent),transparent); height:1px; opacity:.15; }
    .line1 { top:30%; width:100%; animation:moveLine 8s ease-in-out infinite; }
    .line2 { top:65%; width:100%; animation:moveLine 8s ease-in-out infinite 4s; }
    @keyframes moveLine { 0%,100%{opacity:.05}50%{opacity:.2} }

    .card {
      position: relative; z-index: 10;
      width: 420px;
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 44px 40px 36px;
      box-shadow: var(--glow), inset 0 1px 0 rgba(0,200,240,.1);
      animation: cardIn .5s ease both;
    }
    @keyframes cardIn { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }

    /* Top accent bar */
    .card::before {
      content:'';
      position:absolute; top:0; left:20px; right:20px; height:2px;
      background:linear-gradient(90deg,transparent,var(--accent),transparent);
      border-radius:2px;
    }

    .logo {
      display:flex; align-items:center; gap:14px; margin-bottom:32px; justify-content:center;
    }
    .logo-icon {
      width:52px; height:52px;
      background:linear-gradient(135deg,#002244,#0077bb);
      border:1px solid var(--accent); border-radius:10px;
      display:flex; align-items:center; justify-content:center;
      font-size:24px; box-shadow:var(--glow);
      position:relative;
    }
    .logo-icon::after { content:'⚡'; }
    .logo-text { text-align:left; }
    .logo-text h1 {
      font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700;
      color:#fff; letter-spacing:2px; text-transform:uppercase; line-height:1.1;
    }
    .logo-text small {
      font-family:'Share Tech Mono',monospace; font-size:9px;
      color:var(--accent); letter-spacing:2px; text-transform:uppercase;
    }

    .divider {
      height:1px; background:linear-gradient(90deg,transparent,var(--border),transparent);
      margin-bottom:28px;
    }

    .status-bar {
      display:flex; align-items:center; gap:8px; margin-bottom:24px;
      padding:8px 12px;
      background:rgba(0,200,240,.05);
      border:1px solid rgba(0,200,240,.15);
      border-radius:5px;
    }
    .status-dot {
      width:7px; height:7px; border-radius:50%;
      background:var(--green); box-shadow:0 0 6px var(--green);
      animation:pulse 2s ease infinite;
    }
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
    .status-text {
      font-family:'Share Tech Mono',monospace; font-size:10px; color:var(--green); letter-spacing:1px;
    }
    .status-host {
      margin-left:auto; font-family:'Share Tech Mono',monospace; font-size:9px; color:var(--muted);
    }

    .field { margin-bottom:18px; }
    .field label {
      display:block; margin-bottom:6px;
      font-family:'Share Tech Mono',monospace; font-size:9px;
      color:var(--muted); letter-spacing:2px; text-transform:uppercase;
    }
    .field input {
      width:100%; background:rgba(0,0,0,.4);
      border:1px solid var(--border); border-radius:6px;
      padding:11px 14px; color:var(--text);
      font-family:'Exo 2',sans-serif; font-size:14px;
      outline:none; transition:border .2s, box-shadow .2s;
    }
    .field input:focus {
      border-color:var(--accent);
      box-shadow:0 0 10px rgba(0,200,240,.2);
    }
    .field input::placeholder { color:var(--muted); }

    .error-box {
      background:rgba(255,71,87,.1); border:1px solid rgba(255,71,87,.4);
      border-radius:5px; padding:10px 14px; margin-bottom:18px;
      font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--red);
      display:flex; align-items:center; gap:8px;
    }

    .login-btn {
      width:100%; padding:13px;
      background:linear-gradient(135deg,#004488,#0099cc);
      border:1px solid var(--accent); border-radius:7px;
      color:#fff; font-family:'Rajdhani',sans-serif;
      font-size:16px; font-weight:700; letter-spacing:2px;
      text-transform:uppercase; cursor:pointer;
      transition:all .25s; box-shadow:var(--glow);
      margin-top:6px;
    }
    .login-btn:hover { box-shadow:var(--glow2); transform:translateY(-1px); }
    .login-btn:active { transform:translateY(0); }

    .footer-note {
      text-align:center; margin-top:22px;
      font-family:'Share Tech Mono',monospace; font-size:9px; color:var(--muted);
      letter-spacing:1px;
    }
    .security-badges {
      display:flex; gap:8px; justify-content:center; margin-top:14px;
    }
    .s-badge {
      font-family:'Share Tech Mono',monospace; font-size:8px; letter-spacing:1px;
      padding:2px 8px; border-radius:3px; text-transform:uppercase;
      background:rgba(0,200,240,.08); border:1px solid rgba(0,200,240,.2); color:var(--muted);
    }
  </style>
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
</body>
</html>