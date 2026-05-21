<?php
session_start();
require_once __DIR__ . '/../mail-config.php';

// ─── AUTH CHECK ───────────────────────────────────────────
if (empty($_SESSION['mail_auth'])) {
    header('Location: ../mail-login.php'); exit;
}
if (time() - ($_SESSION['login_time'] ?? 0) > 7200) {
    session_destroy();
    header('Location: ../mail-login.php?timeout=1'); exit;
}
$_SESSION['login_time'] = time();

// ─── IMAP FOLDER NAME RESOLVER ────────────────────────────
// cPanel servers use "INBOX.Sent", "INBOX.Drafts", etc.
// Some use "Sent", "Drafts". We try both and cache the result.
function resolveFolder(string $label): string {
    // Cache in session to avoid repeated LIST calls
    if (!isset($_SESSION['folder_map'])) $_SESSION['folder_map'] = [];
    if (isset($_SESSION['folder_map'][$label])) return $_SESSION['folder_map'][$label];

    // Candidates: cPanel-style first, then plain
    $candidates = [
        'Sent'   => ['INBOX.Sent',   'Sent',   'Sent Messages', 'INBOX/Sent'],
        'Drafts' => ['INBOX.Drafts', 'Drafts', 'Draft',         'INBOX/Drafts'],
        'Junk'   => ['INBOX.Junk',   'Junk',   'Spam',          'INBOX.Spam', 'INBOX/Junk'],
        'Trash'  => ['INBOX.Trash',  'Trash',  'Deleted',       'INBOX.Deleted', 'INBOX/Trash'],
    ];

    if (!isset($candidates[$label])) {
        $_SESSION['folder_map'][$label] = $label;
        return $label;
    }

    $base = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}';
    $c = @imap_open($base . 'INBOX', MAIL_USER, MAIL_PASS);
    if (!$c) {
        $_SESSION['folder_map'][$label] = $label;
        return $label;
    }
    $list = @imap_list($c, $base, '*') ?: [];
    imap_close($c);
    $available = array_map(fn($f) => str_replace($base, '', $f), $list);

    foreach ($candidates[$label] as $candidate) {
        foreach ($available as $avail) {
            if (strcasecmp($avail, $candidate) === 0) {
                $_SESSION['folder_map'][$label] = $avail;
                return $avail;
            }
        }
    }

    // Fallback: first partial match
    foreach ($candidates[$label] as $candidate) {
        foreach ($available as $avail) {
            if (stripos($avail, $label) !== false) {
                $_SESSION['folder_map'][$label] = $avail;
                return $avail;
            }
        }
    }

    $_SESSION['folder_map'][$label] = $label;
    return $label;
}

// ─── IMAP CONNECT ─────────────────────────────────────────
function imapConn(string $folder = 'INBOX') {
    $realFolder = in_array($folder, ['Sent','Drafts','Junk','Trash'])
        ? resolveFolder($folder)
        : $folder;
    return @imap_open(
        '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}' . $realFolder,
        MAIL_USER, MAIL_PASS
    );
}

// ─── DECODE ───────────────────────────────────────────────
function dec(string $s): string {
    if (trim($s) === '') return '(no subject)';
    $out = '';
    foreach (imap_mime_header_decode($s) as $p) {
        $text = $p->text;
        if ($p->charset && strtolower($p->charset) !== 'default') {
            $text = @mb_convert_encoding($text, 'UTF-8', $p->charset) ?: $text;
        }
        $out .= $text;
    }
    return htmlspecialchars($out, ENT_QUOTES | ENT_HTML5);
}

// ─── BODY ─────────────────────────────────────────────────
function getBody($c, int $n, $st, string $pn = ''): string {
    if (!isset($st->parts)) {
        $raw = imap_fetchbody($c, $n, $pn ?: '1');
        $text = decPart($raw, $st->encoding ?? 0);
        return strtoupper($st->subtype ?? '') === 'HTML'
            ? $text
            : nl2br(htmlspecialchars($text, ENT_QUOTES | ENT_HTML5));
    }
    $html = $plain = '';
    foreach ($st->parts as $i => $p) {
        $num = ($pn ? "$pn." : '') . ($i + 1);
        $sub = strtoupper($p->subtype ?? '');
        if ($p->type === 0) { // TEXT
            $b = decPart(imap_fetchbody($c, $n, $num), $p->encoding ?? 0);
            if (isset($p->parameters)) {
                foreach ($p->parameters as $param) {
                    if (strtolower($param->attribute) === 'charset' && strtolower($param->value) !== 'utf-8') {
                        $b = @mb_convert_encoding($b, 'UTF-8', $param->value) ?: $b;
                    }
                }
            }
            if ($sub === 'HTML') {
                $html = $b;
            } elseif ($sub === 'PLAIN' && !$plain) {
                $plain = nl2br(htmlspecialchars($b, ENT_QUOTES | ENT_HTML5));
            }
        } elseif ($p->type === 1 || isset($p->parts)) { // MULTIPART
            $s = getBody($c, $n, $p, $num);
            if ($s && !$html) $html = $s;
        }
    }
    return $html ?: $plain ?: '<em style="color:#666">(empty message)</em>';
}

function decPart(string $b, int $e): string {
    if ($e === 3) return base64_decode($b);
    if ($e === 4) return quoted_printable_decode($b);
    return $b;
}

// ─── ATTACHMENTS ──────────────────────────────────────────
function getAtts($st, string $pn = ''): array {
    $a = [];
    if (!isset($st->parts)) return $a;
    foreach ($st->parts as $i => $p) {
        $num = ($pn ? "$pn." : '') . ($i + 1);
        $disp = strtolower($p->disposition ?? '');
        if ($disp === 'attachment' || $disp === 'inline') {
            $name = '';
            foreach (array_merge($p->dparameters ?? [], $p->parameters ?? []) as $x) {
                if (in_array(strtolower($x->attribute), ['filename','name'])) { $name = $x->value; break; }
            }
            if ($name) $a[] = ['name' => $name, 'part' => $num];
        }
        if (isset($p->parts)) $a = array_merge($a, getAtts($p, $num));
    }
    return $a;
}

// ─── SAVE TO SENT (IMAP APPEND) ───────────────────────────
function appendToSent(string $raw): void {
    $sentFolder = resolveFolder('Sent');
    $mbox = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}' . $sentFolder;
    $c = @imap_open($mbox, MAIL_USER, MAIL_PASS);
    if ($c) {
        @imap_append($c, $mbox, $raw, '\\Seen');
        imap_close($c);
    }
}

// ─── SAVE DRAFT (IMAP APPEND) ─────────────────────────────
function saveDraftIMAP(string $to, string $subject, string $body, string $cc = '', string $bcc = ''): array {
    $draftFolder = resolveFolder('Drafts');
    $mbox = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}' . $draftFolder;
    $c = @imap_open($mbox, MAIL_USER, MAIL_PASS);
    if (!$c) return ['ok' => false, 'error' => 'Cannot open Drafts: ' . imap_last_error()];

    $u   = MAIL_USER;
    $h   = MAIL_HOST;
    $full = $body . sigHTML();
    $raw  = "Date: " . date('r') . "\r\n"
          . "From: " . SIG_NAME . " <$u>\r\n"
          . "To: $to\r\n"
          . ($cc  ? "Cc: $cc\r\n"  : '')
          . "Reply-To: $u\r\n"
          . "Message-ID: <" . uniqid('draft.', true) . "@$h>\r\n"
          . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
          . "MIME-Version: 1.0\r\n"
          . "Content-Type: text/html; charset=UTF-8\r\n"
          . "Content-Transfer-Encoding: base64\r\n\r\n"
          . chunk_split(base64_encode($full));

    $ok = @imap_append($c, $mbox, $raw, '\\Draft');
    imap_close($c);
    return $ok ? ['ok' => true] : ['ok' => false, 'error' => imap_last_error()];
}

// ─── SMTP SEND ────────────────────────────────────────────
function buildRaw(string $to, string $subject, string $body, string $cc, string $bcc): string {
    $u    = MAIL_USER;
    $h    = MAIL_HOST;
    $full = $body . sigHTML();
    return "Date: " . date('r') . "\r\n"
         . "From: " . SIG_NAME . " <$u>\r\n"
         . "To: $to\r\n"
         . ($cc  ? "Cc: $cc\r\n"  : '')
         . ($bcc ? "Bcc: $bcc\r\n" : '')
         . "Reply-To: $u\r\n"
         . "Message-ID: <" . uniqid('', true) . "@$h>\r\n"
         . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/html; charset=UTF-8\r\n"
         . "Content-Transfer-Encoding: base64\r\n\r\n"
         . chunk_split(base64_encode($full));
}

function sendSMTP(string $to, string $subject, string $body, string $cc = '', string $bcc = ''): array {
    $h   = MAIL_HOST;
    $u   = MAIL_USER;
    $p   = MAIL_PASS;
    $raw = buildRaw($to, $subject, $body, $cc, $bcc);

    $ctx = stream_context_create(['ssl' => [
        'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true
    ]]);

    $lastErr = ['ok' => false, 'error' => 'No SMTP port available'];
    foreach ([[465, true],[587, false]] as [$port, $ssl]) {
        $r = doSMTP($h, $port, $ssl, $u, $p, $to, $cc, $bcc, $raw, $ctx);
        if ($r['ok']) {
            // Append to Sent folder
            appendToSent($raw);
            return $r;
        }
        $lastErr = $r;
    }
    return $lastErr;
}

function doSMTP($host,$port,$ssl,$u,$p,$to,$cc,$bcc,$raw,$ctx): array {
    try {
        $sock = $ssl
            ? @stream_socket_client("ssl://$host:$port", $en, $er, 15, STREAM_CLIENT_CONNECT, $ctx)
            : @stream_socket_client("tcp://$host:$port", $en, $er, 15);
        if (!$sock) return ['ok'=>false,'error'=>"Port $port: $er"];
        stream_set_timeout($sock, 15);

        $rd = function() use ($sock) {
            $o = '';
            while ($l = fgets($sock, 512)) {
                $o .= $l;
                if (isset($l[3]) && $l[3] === ' ') break;
            }
            return $o;
        };
        $cm = function($c) use ($sock, $rd) { fwrite($sock, "$c\r\n"); return $rd(); };

        $rd(); // banner
        $ehlo = $cm('EHLO ' . (gethostname() ?: 'localhost'));
        if (!$ssl) {
            $cm('STARTTLS');
            stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $cm('EHLO localhost');
        }
        $cm('AUTH LOGIN');
        $cm(base64_encode($u));
        $auth = $cm(base64_encode($p));
        if (!str_starts_with(trim($auth), '235')) {
            fclose($sock);
            return ['ok'=>false,'error'=>"Auth failed (port $port): " . trim($auth)];
        }
        $cm("MAIL FROM:<$u>");
        $recipients = array_filter(array_map('trim', explode(',', "$to,$cc,$bcc")));
        foreach ($recipients as $r) {
            if (filter_var($r, FILTER_VALIDATE_EMAIL)) $cm("RCPT TO:<$r>");
        }
        $cm('DATA');
        fwrite($sock, "$raw\r\n.\r\n");
        $resp = $rd();
        $cm('QUIT');
        fclose($sock);
        return str_starts_with(trim($resp), '250')
            ? ['ok'=>true,'port'=>$port]
            : ['ok'=>false,'error'=>"Send rejected (port $port): " . trim($resp)];
    } catch(\Throwable $e) {
        return ['ok'=>false,'error'=>$e->getMessage()];
    }
}

// ─── SIGNATURE ────────────────────────────────────────────
function sigHTML(): string {
    [$n,$t,$e,$w,$ph,$loc,$li] = [SIG_NAME,SIG_TITLE,SIG_EMAIL,SIG_WEBSITE,SIG_PHONE,SIG_LOCATION,SIG_LINKEDIN];
    return "<br><br>
<table cellpadding='0' cellspacing='0' style='font-family:Arial,sans-serif;border-left:3px solid #00aadd;background:#0a0f1a;color:#c8dff0;max-width:500px;'>
<tr><td style='padding:14px 18px;'>
<p style='margin:0 0 2px;font-size:18px;font-weight:700;color:#fff;'>$n</p>
<p style='margin:0 0 12px;font-size:11px;color:#00aadd;letter-spacing:2px;text-transform:uppercase;'>⚡ $t</p>
<hr style='border:none;border-top:1px solid #1e3a5f;margin-bottom:10px;'>
<table cellpadding='3' cellspacing='0' style='font-size:12px;width:100%;'>
<tr><td>📧</td><td><a href='mailto:$e' style='color:#c8dff0;'>$e</a></td><td>🌐</td><td><a href='$w' style='color:#c8dff0;'>$w</a></td></tr>
<tr><td>📞</td><td style='color:#c8dff0;'>$ph</td><td>📍</td><td style='color:#c8dff0;'>$loc</td></tr>
<tr><td>🔗</td><td colspan='3'><a href='$li' style='color:#c8dff0;'>LinkedIn Profile</a></td></tr>
</table><br>
<span style='background:#0d2240;border:1px solid #00aadd;border-radius:3px;padding:2px 7px;font-size:9px;color:#00aadd;margin-right:4px;'>B.E. ELECTRICAL</span>
<span style='background:#0d2240;border:1px solid #00aadd;border-radius:3px;padding:2px 7px;font-size:9px;color:#00aadd;margin-right:4px;'>POWER SYSTEMS</span>
<span style='background:#241800;border:1px solid #ff9500;border-radius:3px;padding:2px 7px;font-size:9px;color:#ff9500;margin-right:4px;'>NEC LICENSED</span>
<span style='background:#0d2240;border:1px solid #00aadd;border-radius:3px;padding:2px 7px;font-size:9px;color:#00aadd;margin-right:4px;'>HV ENGINEERING</span>
<span style='background:#241800;border:1px solid #ff9500;border-radius:3px;padding:2px 7px;font-size:9px;color:#ff9500;'>IEEE MEMBER</span>
</td></tr></table>";
}

// ─── AJAX ─────────────────────────────────────────────────
if (isset($_GET['ajax'])) {
    // Suppress ALL output before we start — catches PHP warnings/notices
    // that would corrupt JSON (the #1 cause of "Unexpected end of JSON input")
    ob_start();
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);

    // Register a shutdown handler so fatal errors also return valid JSON
    register_shutdown_function(function() {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            ob_clean();
            if (!headers_sent()) header('Content-Type: application/json');
            echo json_encode(['error' => 'PHP fatal: ' . $err['message'] . ' in ' . basename($err['file']) . ':' . $err['line']]);
        }
    });

    header('Content-Type: application/json');
    $ajax   = $_GET['ajax'];
    $folder = $_GET['folder'] ?? 'INBOX';

    // Wrap everything so any exception also returns JSON
    try {

    if ($ajax === 'logout') {
        session_destroy();
        ob_clean(); echo json_encode(['ok'=>true]); exit;
    }
    if ($ajax === 'signature') {
        ob_clean(); echo json_encode(['html' => sigHTML()]); exit;
    }
    if ($ajax === 'folders') {
        $base = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}';
        $c = @imap_open($base . 'INBOX', MAIL_USER, MAIL_PASS);
        if (!$c) { ob_clean(); echo json_encode(['error' => imap_last_error() ?: 'IMAP connect failed']); exit; }
        $list = @imap_list($c, $base, '*') ?: [];
        $folders = array_map(fn($f) => str_replace($base, '', $f), $list);
        imap_close($c);
        unset($_SESSION['folder_map']);
        ob_clean(); echo json_encode(['folders' => $folders]); exit;
    }
    if ($ajax === 'debug') {
        $h = MAIL_HOST; $u = MAIL_USER; $p = MAIL_PASS;
        $out = [
            'host'     => $h,
            'user'     => $u,
            'php'      => PHP_VERSION,
            'imap_ext' => extension_loaded('imap')    ? 'YES' : 'NO',
            'openssl'  => extension_loaded('openssl')  ? 'YES' : 'NO',
            'mbstring' => extension_loaded('mbstring') ? 'YES' : 'NO',
        ];
        if (extension_loaded('imap')) {
            $c = @imap_open("{{$h}:993/imap/ssl/novalidate-cert}INBOX", $u, $p);
            $out['imap_993'] = $c ? 'OK — Connected!' : 'FAILED: ' . (imap_last_error() ?: 'unknown');
            if ($c) {
                $base  = "{{$h}:993/imap/ssl/novalidate-cert}";
                $list  = @imap_list($c, $base, '*') ?: [];
                $names = array_map(fn($f) => str_replace($base, '', $f), $list);
                $out['folders_found']   = implode(' | ', array_slice($names, 0, 12));
                $out['sent_resolved']   = resolveFolder('Sent');
                $out['drafts_resolved'] = resolveFolder('Drafts');
                $out['junk_resolved']   = resolveFolder('Junk');
                $out['trash_resolved']  = resolveFolder('Trash');
                imap_close($c);
            }
        }
        $ctx = stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]]);
        $s = @stream_socket_client("ssl://$h:465", $en, $er, 8, STREAM_CLIENT_CONNECT, $ctx);
        if ($s) {
            fgets($s, 512);
            fwrite($s, "EHLO localhost\r\n");
            while ($l = fgets($s, 512)) { if (isset($l[3]) && $l[3] === ' ') break; }
            fwrite($s, "AUTH LOGIN\r\n"); fgets($s, 512);
            fwrite($s, base64_encode($u) . "\r\n"); fgets($s, 512);
            fwrite($s, base64_encode($p) . "\r\n");
            $out['smtp_465'] = 'OK'; $out['smtp_465_auth'] = trim(fgets($s, 512)); fclose($s);
        } else $out['smtp_465'] = "FAILED: $er";
        $s2 = @stream_socket_client("tcp://$h:587", $en2, $er2, 8);
        if ($s2) {
            fgets($s2, 512); fwrite($s2, "EHLO localhost\r\n");
            while ($l = fgets($s2, 512)) { if (isset($l[3]) && $l[3] === ' ') break; }
            fwrite($s2, "STARTTLS\r\n"); fgets($s2, 512);
            @stream_socket_enable_crypto($s2, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fwrite($s2, "EHLO localhost\r\n");
            while ($l = fgets($s2, 512)) { if (isset($l[3]) && $l[3] === ' ') break; }
            fwrite($s2, "AUTH LOGIN\r\n"); fgets($s2, 512);
            fwrite($s2, base64_encode($u) . "\r\n"); fgets($s2, 512);
            fwrite($s2, base64_encode($p) . "\r\n");
            $out['smtp_587'] = 'OK'; $out['smtp_587_auth'] = trim(fgets($s2, 512)); fclose($s2);
        } else $out['smtp_587'] = "FAILED: $er2";
        ob_clean(); echo json_encode($out, JSON_PRETTY_PRINT); exit;
    }

    // ── DRAFT SAVE ──
    if ($ajax === 'draft') {
        $to   = trim($_POST['to']      ?? '');
        $sub  = trim($_POST['subject'] ?? '');
        $body = $_POST['body']         ?? '';
        $cc   = trim($_POST['cc']      ?? '');
        $bcc  = trim($_POST['bcc']     ?? '');
        ob_clean(); echo json_encode(saveDraftIMAP($to, $sub, $body, $cc, $bcc)); exit;
    }

    // ── IMAP REQUIRED FROM HERE ──
    $c = imapConn($folder);
    if (!$c) {
        $err = imap_last_error() ?: 'Check IMAP host/credentials/folder name';
        ob_clean(); echo json_encode(['error' => "IMAP ($folder): $err"]); exit;
    }

    $result = null;
    switch ($ajax) {
        case 'list':
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $search = trim($_GET['search'] ?? '') ?: 'ALL';
            $total  = imap_num_msg($c);
            $info   = imap_mailboxmsginfo($c);
            if (!$total) { $result = ['mails'=>[],'total'=>0,'unread'=>(int)($info->Unread??0)]; break; }
            $uids   = @imap_search($c, $search, SE_UID) ?: [];
            $uids   = array_reverse($uids);
            $found  = count($uids);
            $paged  = array_slice($uids, ($page - 1) * ITEMS_PER_PAGE, ITEMS_PER_PAGE);
            $mails  = [];
            foreach ($paged as $uid) {
    $no = imap_msgno($c, $uid);
    if (!$no) continue;

    $h = @imap_headerinfo($c, $no);
    if (!$h) continue;

    // FIXED FLAGS
    $overview = imap_fetch_overview($c, (string)$no, 0);
    $ov = $overview[0] ?? null;

    $seen     = isset($ov->seen) ? (bool)$ov->seen : false;
    $flagged  = isset($ov->flagged) ? (bool)$ov->flagged : false;
    $answered = isset($ov->answered) ? (bool)$ov->answered : false;

    $from = $h->from[0] ?? null;

    $nm = $from
        ? (imap_utf8($from->personal ?? '') ?: ($from->mailbox . '@' . $from->host))
        : 'Unknown';

    $mails[] = [
        'uid'      => $uid,
        'from'     => htmlspecialchars($nm, ENT_QUOTES | ENT_HTML5),
        'email'    => $from ? ($from->mailbox . '@' . $from->host) : '',
        'subject'  => dec($h->subject ?? ''),
        'date'     => date('d M, H:i', strtotime($h->date ?? 'now')),
        'unread'   => !$seen,
        'flagged'  => $flagged,
        'answered' => $answered,
    ];
            }
            $result = ['mails'=>$mails,'total'=>$found,'unread'=>(int)($info->Unread??0)];
            break;

        case 'read':
            $uid = (int)($_GET['uid'] ?? 0);
            $no  = imap_msgno($c, $uid);
            if (!$no) { $result = ['error'=>'Message not found']; break; }
            $h    = imap_headerinfo($c, $no);
            $st   = imap_fetchstructure($c, $no);
            imap_setflag_full($c, (string)$uid, '\\Seen', ST_UID);
            $from = $h->from[0] ?? null;
            $rto  = $h->to[0]   ?? null;
            $cc   = isset($h->cc) ? array_map(fn($x) => $x->mailbox . '@' . $x->host, $h->cc) : [];
            $result = [
                'uid'         => $uid,
                'subject'     => dec($h->subject ?? ''),
                'from'        => htmlspecialchars($from ? imap_utf8($from->personal ?? '') : 'Unknown', ENT_QUOTES | ENT_HTML5),
                'from_email'  => $from ? ($from->mailbox . '@' . $from->host) : '',
                'to'          => $rto ? ($rto->mailbox . '@' . $rto->host) : MAIL_USER,
                'cc'          => implode(', ', $cc),
                'date'        => date('l, d F Y \a\t H:i', strtotime($h->date ?? 'now')),
                'body'        => getBody($c, $no, $st),
                'attachments' => getAtts($st),
            ];
            break;

        case 'delete':
            $uid = (int)($_GET['uid'] ?? 0);
            imap_delete($c, (string)$uid, FT_UID);
            $result = ['ok' => (bool)imap_expunge($c)];
            break;

        case 'flag':
            $uid = (int)($_GET['uid'] ?? 0);
            $on  = ($_GET['state'] ?? '1') === '1';
            $on
                ? imap_setflag_full($c, (string)$uid, '\\Flagged', ST_UID)
                : imap_clearflag_full($c, (string)$uid, '\\Flagged', ST_UID);
            $result = ['ok' => true];
            break;

        case 'mark_read':
            $uid = (int)($_GET['uid'] ?? 0);
            imap_setflag_full($c, (string)$uid, '\\Seen', ST_UID);
            $result = ['ok' => true];
            break;

        case 'mark_unread':
            $uid = (int)($_GET['uid'] ?? 0);
            imap_clearflag_full($c, (string)$uid, '\\Seen', ST_UID);
            $result = ['ok' => true];
            break;

        case 'move':
            $uid  = (int)($_GET['uid'] ?? 0);
            $dest = $_GET['dest'] ?? 'Trash';
            if (in_array($dest, ['Sent','Drafts','Junk','Trash'])) $dest = resolveFolder($dest);
            imap_mail_move($c, (string)$uid, $dest, CP_UID);
            $result = ['ok' => (bool)imap_expunge($c)];
            break;

        case 'send':
            $to   = trim($_POST['to']      ?? '');
            $sub  = trim($_POST['subject'] ?? '');
            $body = $_POST['body']         ?? '';
            $cc   = trim($_POST['cc']      ?? '');
            $bcc  = trim($_POST['bcc']     ?? '');
            if (!$to)  { $result = ['ok'=>false,'error'=>'To field is empty']; break; }
            if (!$sub) { $result = ['ok'=>false,'error'=>'Subject is empty'];  break; }
            $badAddr = null;
            foreach (array_map('trim', explode(',', $to)) as $addr) {
                if ($addr && !filter_var($addr, FILTER_VALIDATE_EMAIL)) { $badAddr = $addr; break; }
            }
            if ($badAddr) { $result = ['ok'=>false,'error'=>"Invalid email: $badAddr"]; break; }
            $result = sendSMTP($to, $sub, $body, $cc, $bcc);
            break;

        case 'count':
            $info   = imap_mailboxmsginfo($c);
            $result = ['total'=>(int)imap_num_msg($c),'unread'=>(int)($info->Unread??0)];
            break;

        default:
            $result = ['error' => 'Unknown action: ' . htmlspecialchars($ajax)];
    }

    imap_close($c);
    ob_clean();
    echo json_encode($result ?? ['error' => 'No result']);
    exit;

    } catch (\Throwable $e) {
        ob_clean();
        echo json_encode(['error' => 'Exception: ' . $e->getMessage() . ' (line ' . $e->getLine() . ')']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Mail Admin — <?= SIG_NAME ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@600;700&family=Exo+2:wght@300;400;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#060a10;--panel:#0b1422;--panel2:#0f1c2e;--border:#1a3354;--accent:#00c8f0;--accent2:#ff9500;--green:#00ff88;--red:#ff4757;--yellow:#ffd700;--text:#c0d8ee;--muted:#3a5a7a;--glow:0 0 14px rgba(0,200,240,.35);--glow2:0 0 24px rgba(0,200,240,.6);}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--bg);color:var(--text);font-family:'Exo 2',sans-serif;height:100vh;overflow:hidden;
      background-image:repeating-linear-gradient(0deg,transparent,transparent 49px,rgba(26,51,84,.12) 50px),
      repeating-linear-gradient(90deg,transparent,transparent 49px,rgba(26,51,84,.12) 50px);}
    body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:999;
      background:repeating-linear-gradient(0deg,transparent 0,transparent 2px,rgba(0,0,0,.04) 2px,rgba(0,0,0,.04) 4px);}

    /* TOPBAR */
    .topbar{display:flex;align-items:center;justify-content:space-between;padding:10px 22px;
      background:rgba(11,20,34,.97);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;height:58px;}
    .brand{display:flex;align-items:center;gap:11px;}
    .brand-ico{width:36px;height:36px;background:linear-gradient(135deg,#002244,#0077bb);border:1px solid var(--accent);
      border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:17px;box-shadow:var(--glow);}
    .brand-name h1{font-family:'Rajdhani',sans-serif;font-size:16px;font-weight:700;color:var(--accent);letter-spacing:2px;text-transform:uppercase;}
    .brand-name small{font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);letter-spacing:1px;}
    .topbar-r{display:flex;align-items:center;gap:12px;}
    .status{display:flex;align-items:center;gap:5px;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--green);}
    .dot{width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 5px var(--green);animation:blink 2s infinite;}
    @keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
    .acct{background:rgba(0,200,240,.08);border:1px solid rgba(0,200,240,.3);border-radius:20px;
      padding:3px 11px;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--accent);}
    .clock{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);}
    .tbtn{background:rgba(26,51,84,.5);border:1px solid var(--border);border-radius:5px;padding:5px 12px;
      color:var(--text);font-size:11px;cursor:pointer;transition:all .2s;font-family:'Exo 2',sans-serif;display:flex;align-items:center;gap:5px;}
    .tbtn:hover{border-color:var(--accent);color:var(--accent);}
    .tbtn.compose{background:linear-gradient(135deg,#004488,#0099cc);border-color:var(--accent);color:#fff;
      font-family:'Rajdhani',sans-serif;font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:6px 16px;}
    .tbtn.compose:hover{box-shadow:var(--glow2);}
    .tbtn.danger:hover{border-color:var(--red);color:var(--red);}
    .tbtn.dbg{border-color:rgba(255,149,0,.4);color:var(--accent2);font-family:'Share Tech Mono',monospace;font-size:9px;}

    /* LAYOUT */
    .layout{display:grid;grid-template-columns:200px 290px 1fr;height:calc(100vh - 58px);}

    /* SIDEBAR */
    .sidebar{background:var(--panel);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;}
    .sb-section{padding:10px 14px 4px;font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-top:8px;}
    .nav{display:flex;align-items:center;gap:8px;padding:8px 14px;cursor:pointer;
      transition:all .15s;border-left:2px solid transparent;font-size:12px;font-weight:500;color:var(--muted);}
    .nav:hover{background:rgba(0,200,240,.06);color:var(--text);}
    .nav.active{background:rgba(0,200,240,.1);border-left-color:var(--accent);color:var(--accent);}
    .nav-ico{font-size:13px;width:16px;text-align:center;}
    .nbadge{margin-left:auto;background:var(--accent2);color:#000;font-size:8px;font-weight:700;
      border-radius:10px;padding:1px 6px;font-family:'Share Tech Mono',monospace;min-width:18px;text-align:center;}
    .sb-user{margin:auto 12px 14px;padding:10px;background:rgba(0,0,0,.3);border:1px solid var(--border);border-radius:6px;}
    .sb-user-name{font-size:11px;font-weight:600;color:var(--text);margin-bottom:2px;}
    .sb-user-email{font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);}
    .storage{margin:0 12px 14px;padding:8px 10px;background:rgba(0,0,0,.3);border:1px solid var(--border);border-radius:5px;}
    .st-lbl{display:flex;justify-content:space-between;font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);margin-bottom:4px;}
    .st-bar{height:3px;background:rgba(255,255,255,.08);border-radius:2px;}
    .st-fill{height:100%;width:34%;background:linear-gradient(90deg,var(--accent),var(--accent2));border-radius:2px;}

    /* MAIL LIST */
    .ml{border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;}
    .ml-head{display:flex;align-items:center;gap:7px;padding:10px 13px;border-bottom:1px solid var(--border);background:var(--panel);}
    .ml-title{font-family:'Rajdhani',sans-serif;font-size:14px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--accent);}
    .ml-count{font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);background:rgba(0,0,0,.3);
      border:1px solid var(--border);border-radius:10px;padding:2px 7px;margin-left:auto;}
    .ml-toolbar{display:flex;align-items:center;gap:6px;padding:7px 11px;border-bottom:1px solid var(--border);background:var(--panel2);}
    .search-inp{flex:1;background:rgba(0,0,0,.4);border:1px solid var(--border);border-radius:4px;
      padding:6px 9px;color:var(--text);font-family:'Share Tech Mono',monospace;font-size:9px;outline:none;transition:border .2s;}
    .search-inp:focus{border-color:var(--accent);}
    .search-inp::placeholder{color:var(--muted);}
    .sm-btn{background:rgba(26,51,84,.4);border:1px solid var(--border);border-radius:4px;padding:5px 8px;
      color:var(--muted);cursor:pointer;font-size:10px;transition:all .15s;white-space:nowrap;}
    .sm-btn:hover{border-color:var(--accent);color:var(--accent);}
    .mails{overflow-y:auto;flex:1;}
    .mi{padding:10px 12px;border-bottom:1px solid rgba(26,51,84,.35);cursor:pointer;transition:background .12s;display:grid;gap:2px;position:relative;}
    .mi::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;}
    .mi.unread::before{background:var(--accent);}
    .mi.flagged::before{background:var(--accent2);}
    .mi.answered::before{background:var(--green);}
    .mi:hover,.mi.active{background:rgba(0,200,240,.07);}
    .mi.active{background:rgba(0,200,240,.13);}
    .mi-r1{display:flex;align-items:center;justify-content:space-between;gap:5px;}
    .mi-from{font-size:11px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .mi.unread .mi-from{color:#fff;}
    .mi-time{font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);flex-shrink:0;}
    .mi-subj{font-size:10px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .mi.unread .mi-subj{color:var(--text);}
    .info{text-align:center;padding:20px 14px;font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);}
    .err{color:var(--red);padding:14px;font-family:'Share Tech Mono',monospace;font-size:10px;text-align:center;}
    .pg{display:flex;gap:4px;padding:6px 10px;border-top:1px solid var(--border);background:var(--panel);flex-wrap:wrap;}
    .pgb{background:rgba(26,51,84,.3);border:1px solid var(--border);border-radius:3px;padding:3px 8px;
      color:var(--muted);cursor:pointer;font-family:'Share Tech Mono',monospace;font-size:8px;}
    .pgb.cur{border-color:var(--accent);color:var(--accent);background:rgba(0,200,240,.12);}

    /* MAIL VIEW */
    .mv{display:flex;flex-direction:column;overflow:hidden;}
    .mv-head{padding:14px 20px 12px;border-bottom:1px solid var(--border);background:var(--panel);}
    .mv-subj{font-family:'Rajdhani',sans-serif;font-size:18px;font-weight:700;color:#fff;margin-bottom:10px;line-height:1.2;}
    .meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .meta-b{display:flex;flex-direction:column;}
    .meta-l{font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);letter-spacing:1px;text-transform:uppercase;}
    .meta-v{font-size:11px;color:var(--text);}
    .msep{width:1px;height:24px;background:var(--border);}
    .mv-actions{display:flex;gap:6px;padding:7px 18px;border-bottom:1px solid var(--border);background:rgba(11,20,34,.8);flex-wrap:wrap;}
    .ab{background:rgba(26,51,84,.4);border:1px solid var(--border);border-radius:4px;padding:5px 12px;
      color:var(--text);font-size:11px;cursor:pointer;transition:all .15s;font-family:'Exo 2',sans-serif;display:flex;align-items:center;gap:4px;}
    .ab:hover{border-color:var(--accent);color:var(--accent);}
    .ab.pri{background:rgba(0,68,136,.4);border-color:var(--accent);color:var(--accent);}
    .ab.red:hover{border-color:var(--red);color:var(--red);}
    .mv-body{flex:1;overflow-y:auto;padding:22px 24px;line-height:1.8;font-size:13px;color:var(--text);}
    .mv-body img{max-width:100%;}
    .mv-body a{color:var(--accent);}
    .atts{display:flex;gap:6px;padding:8px 18px;border-top:1px solid var(--border);flex-wrap:wrap;background:var(--panel2);}
    .att{background:rgba(0,200,240,.07);border:1px solid var(--border);border-radius:4px;padding:4px 10px;
      font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);cursor:pointer;}
    .att:hover{border-color:var(--accent);color:var(--accent);}
    .empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:10px;
      color:var(--muted);font-family:'Share Tech Mono',monospace;font-size:10px;}
    .empty-ico{font-size:42px;opacity:.12;}

    /* COMPOSE */
    .overlay{display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.7);backdrop-filter:blur(5px);
      align-items:center;justify-content:center;}
    .overlay.open{display:flex;}
    .compose{width:640px;max-width:96vw;background:var(--panel);border:1px solid var(--accent);border-radius:10px;
      box-shadow:0 0 40px rgba(0,200,240,.2);display:flex;flex-direction:column;max-height:88vh;animation:pop .25s ease;}
    @keyframes pop{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
    .c-head{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;
      background:rgba(0,68,136,.25);border-bottom:1px solid var(--border);}
    .c-title{font-family:'Rajdhani',sans-serif;font-size:14px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--accent);}
    .x-btn{background:none;border:none;color:var(--muted);cursor:pointer;font-size:18px;line-height:1;transition:color .2s;}
    .x-btn:hover{color:var(--red);}
    .c-fields{padding:12px 15px;display:flex;flex-direction:column;gap:6px;border-bottom:1px solid var(--border);}
    .f-row{display:flex;align-items:center;gap:8px;border-bottom:1px solid rgba(26,51,84,.5);padding-bottom:5px;}
    .f-row:last-child{border-bottom:none;padding-bottom:0;}
    .f-l{font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);letter-spacing:1px;text-transform:uppercase;min-width:32px;}
    .f-i{flex:1;background:none;border:none;color:var(--text);font-size:12px;font-family:'Exo 2',sans-serif;outline:none;}
    .c-tools{display:flex;gap:6px;padding:7px 14px;border-bottom:1px solid var(--border);background:var(--panel2);}
    .tool-btn{background:rgba(26,51,84,.4);border:1px solid var(--border);border-radius:3px;padding:3px 8px;
      font-size:10px;color:var(--muted);cursor:pointer;transition:all .15s;font-family:'Share Tech Mono',monospace;}
    .tool-btn:hover{border-color:var(--accent);color:var(--accent);}
    .c-body{flex:1;padding:14px 16px;font-size:13px;color:var(--text);background:rgba(0,0,0,.2);
      min-height:180px;font-family:'Exo 2',sans-serif;line-height:1.7;outline:none;overflow-y:auto;}
    .c-foot{display:flex;align-items:center;gap:8px;padding:10px 15px;border-top:1px solid var(--border);background:var(--panel2);}
    .send-btn{background:linear-gradient(135deg,#004488,#0099cc);border:1px solid var(--accent);border-radius:5px;
      padding:8px 24px;color:#fff;font-family:'Rajdhani',sans-serif;font-size:14px;font-weight:700;letter-spacing:1px;
      text-transform:uppercase;cursor:pointer;transition:all .2s;}
    .send-btn:hover{box-shadow:var(--glow2);}
    .send-btn:disabled{opacity:.5;cursor:not-allowed;}
    .char-count{margin-left:auto;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);}

    /* DEBUG PANEL */
    .dbg-panel{position:fixed;top:64px;right:16px;background:var(--panel);border:1px solid var(--accent);
      border-radius:8px;padding:16px;z-index:400;width:460px;max-height:75vh;overflow-y:auto;
      font-family:'Share Tech Mono',monospace;font-size:10px;box-shadow:0 0 24px rgba(0,200,240,.2);display:none;}
    .dbg-panel.open{display:block;}
    .dbg-row{display:flex;gap:8px;padding:3px 0;border-bottom:1px solid rgba(26,51,84,.3);}
    .dbg-k{color:var(--muted);min-width:160px;flex-shrink:0;}
    .ok{color:var(--green);} .fail{color:var(--red);} .warn{color:var(--accent2);}

    /* MOVE DIALOG */
    .move-dlg{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
      background:var(--panel);border:1px solid var(--accent);border-radius:8px;padding:20px;z-index:300;
      min-width:280px;display:none;}
    .move-dlg.open{display:block;}
    .move-dlg h3{font-family:'Rajdhani',sans-serif;color:var(--accent);margin-bottom:12px;letter-spacing:1px;}
    .move-opt{padding:8px 12px;cursor:pointer;border-radius:4px;font-size:13px;transition:background .15s;}
    .move-opt:hover{background:rgba(0,200,240,.1);color:var(--accent);}

    /* TOAST */
    .toast{position:fixed;bottom:20px;right:20px;background:var(--panel);border:1px solid var(--green);
      border-radius:6px;padding:9px 16px;font-family:'Share Tech Mono',monospace;font-size:10px;
      color:var(--green);z-index:500;animation:fadeUp .3s;box-shadow:0 0 12px rgba(0,255,136,.25);}
    .toast.err{border-color:var(--red);color:var(--red);}
    @keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

    .corner{position:fixed;width:14px;height:14px;border-color:var(--accent);border-style:solid;opacity:.18;pointer-events:none;z-index:998;}
    .tl{top:5px;left:5px;border-width:2px 0 0 2px}.tr{top:5px;right:5px;border-width:2px 2px 0 0}
    .bl{bottom:5px;left:5px;border-width:0 0 2px 2px}.br{bottom:5px;right:5px;border-width:0 2px 2px 0}
    ::-webkit-scrollbar{width:4px}::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px}
    ::-webkit-scrollbar-thumb:hover{background:var(--muted)}
  </style>
</head>
<body>
<div class="corner tl"></div><div class="corner tr"></div>
<div class="corner bl"></div><div class="corner br"></div>

<!-- TOPBAR -->
<div class="topbar">
  <div class="brand">
    <div class="brand-ico">⚡</div>
    <div class="brand-name"><h1>Mail Admin</h1><small>pradipsubedi1.com.np</small></div>
  </div>
  <div class="topbar-r">
    <div class="status"><div class="dot"></div>SSL LIVE</div>
    <span class="clock" id="clock">--:--:--</span>
    <button class="tbtn dbg" onclick="toggleDebug()">🔧 Debug</button>
    <div class="acct"><?= MAIL_USER ?></div>
    <button class="tbtn compose" onclick="openCompose()">⚡ Compose</button>
    <button class="tbtn danger" onclick="logout()">⏻ Logout</button>
  </div>
</div>

<!-- DEBUG -->
<div class="dbg-panel" id="dbgPanel">
  <div style="color:var(--accent);font-size:12px;font-weight:700;margin-bottom:10px;letter-spacing:1px;">🔧 SERVER DEBUG</div>
  <div id="dbgOut"><span class="warn">Click Debug to run…</span></div>
  <button class="tbtn" onclick="toggleDebug()" style="margin-top:10px;width:100%;justify-content:center;">✕ Close</button>
</div>

<!-- MOVE DIALOG -->
<div class="move-dlg" id="moveDlg">
  <h3>📁 Move To Folder</h3>
  <div id="moveFolders"></div>
  <button class="tbtn" onclick="closeMoveDialog()" style="margin-top:10px;width:100%;justify-content:center;">Cancel</button>
</div>

<!-- LAYOUT -->
<div class="layout">

  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sb-section">FOLDERS</div>
    <div class="nav active" data-folder="INBOX"   onclick="switchFolder(this)"><span class="nav-ico">📥</span>Inbox<span class="nbadge" id="badge-unread">…</span></div>
    <div class="nav"        data-folder="Sent"    onclick="switchFolder(this)"><span class="nav-ico">📤</span>Sent</div>
    <div class="nav"        data-folder="Drafts"  onclick="switchFolder(this)"><span class="nav-ico">📝</span>Drafts</div>
    <div class="nav"        data-folder="Junk"    onclick="switchFolder(this)"><span class="nav-ico">🚫</span>Spam</div>
    <div class="nav"        data-folder="Trash"   onclick="switchFolder(this)"><span class="nav-ico">🗑️</span>Trash</div>

    <div class="sb-section">QUICK FILTER</div>
    <div class="nav" onclick="doSearch('UNSEEN')"                      ><span class="nav-ico" style="color:var(--accent)">◆</span>Unread</div>
    <div class="nav" onclick="doSearch('FLAGGED')"                     ><span class="nav-ico" style="color:var(--accent2)">◆</span>Starred</div>
    <div class="nav" onclick="doSearch('ANSWERED')"                    ><span class="nav-ico" style="color:var(--green)">◆</span>Replied</div>
    <div class="nav" onclick="doSearch('SUBJECT \"tender\"')"          ><span class="nav-ico" style="color:var(--yellow)">◆</span>Tenders</div>
    <div class="nav" onclick="doSearch('SUBJECT \"project\"')"         ><span class="nav-ico" style="color:var(--accent)">◆</span>Projects</div>
    <div class="nav" onclick="doSearch('SUBJECT \"invoice\"')"         ><span class="nav-ico" style="color:var(--accent2)">◆</span>Invoices</div>

    <div class="sb-user">
      <div class="sb-user-name"><?= SIG_NAME ?></div>
      <div class="sb-user-email"><?= MAIL_USER ?></div>
    </div>
    <div class="storage">
      <div class="st-lbl"><span>STORAGE</span><span style="color:var(--accent)">cPanel</span></div>
      <div class="st-bar"><div class="st-fill"></div></div>
    </div>
  </div>

  <!-- MAIL LIST -->
  <div class="ml">
    <div class="ml-head">
      <span class="ml-title" id="folderName">INBOX</span>
      <span class="ml-count" id="mlCount">…</span>
    </div>
    <div class="ml-toolbar">
      <input class="search-inp" id="searchInp" placeholder="🔍 Search…" oninput="debSearch(this.value)"/>
      <button class="sm-btn" onclick="doSearch('UNSEEN')" title="Unread">🔵</button>
      <button class="sm-btn" onclick="doSearch('FLAGGED')" title="Starred">⭐</button>
      <button class="sm-btn" onclick="loadMails(null,1,'ALL');document.getElementById('searchInp').value=''" title="Clear">✕</button>
    </div>
    <div class="mails" id="mailList"><div class="info">⚡ Connecting…</div></div>
    <div class="pg" id="pgBar"></div>
  </div>

  <!-- MAIL VIEW -->
  <div class="mv" id="mailView">
    <div class="empty"><div class="empty-ico">⚡</div><div>SELECT A MESSAGE TO READ</div></div>
  </div>

</div>

<!-- COMPOSE -->
<div class="overlay" id="overlay">
  <div class="compose">
    <div class="c-head">
      <span class="c-title">⚡ New Message</span>
      <button class="x-btn" onclick="closeCompose()">✕</button>
    </div>
    <div class="c-fields">
      <div class="f-row"><span class="f-l">FROM</span><span style="font-size:11px;color:var(--accent);font-family:'Share Tech Mono',monospace;"><?= MAIL_USER ?></span></div>
      <div class="f-row"><span class="f-l">TO</span><input class="f-i" id="cTo" placeholder="recipient@example.com"/></div>
      <div class="f-row"><span class="f-l">CC</span><input class="f-i" id="cCc" placeholder="Optional…"/></div>
      <div class="f-row"><span class="f-l">BCC</span><input class="f-i" id="cBcc" placeholder="Optional…"/></div>
      <div class="f-row"><span class="f-l">SUB</span><input class="f-i" id="cSub" placeholder="Subject…"/></div>
    </div>
    <div class="c-tools">
      <button class="tool-btn" onclick="fmt('bold')"><b>B</b></button>
      <button class="tool-btn" onclick="fmt('italic')"><i>I</i></button>
      <button class="tool-btn" onclick="fmt('underline')"><u>U</u></button>
      <button class="tool-btn" onclick="insertLink()">🔗</button>
      <button class="tool-btn" onclick="insertSig()">⚡ Sig</button>
      <button class="tool-btn" onclick="clearBody()">🗑 Clear</button>
    </div>
    <div class="c-body" id="cBody" contenteditable="true" oninput="updateCharCount()"></div>
    <div class="c-foot">
      <button class="send-btn" id="sendBtn" onclick="sendMail()">Send ▶</button>
      <button class="tbtn" onclick="saveDraft()">💾 Draft</button>
      <span class="char-count" id="charCount">0 chars</span>
      <button class="tbtn" style="margin-left:auto;" onclick="closeCompose()">Cancel</button>
    </div>
  </div>
</div>

<script>
let curFolder='INBOX', curPage=1, curSearch='ALL', curUID=null, searchTimer, folders=[];

// CLOCK
setInterval(()=>{
  const n=new Date();
  document.getElementById('clock').textContent=n.toLocaleTimeString('en-GB',{hour12:false});
},1000);

// LOAD MAILS
async function loadMails(folder, page, search) {
  curFolder = folder ?? curFolder;
  curPage   = page   ?? 1;
  curSearch = search ?? 'ALL';
  document.getElementById('mailList').innerHTML='<div class="info">⚡ Loading…</div>';
  document.getElementById('mlCount').textContent='…';
  try {
    const r = await fetch(url('list',{folder:curFolder,page:curPage,search:curSearch}));
    const d = await r.json();
    if (d.error){ showErr(d.error); return; }
    const unread = d.unread || 0;
    document.getElementById('mlCount').textContent=`${d.total} · ${unread} unread`;
    document.getElementById('badge-unread').textContent=unread||'0';
    renderList(d.mails);
    renderPg(d.total);
    if (d.mails.length) readMail(d.mails[0].uid);
    else document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">📭</div><div>NO MESSAGES IN THIS FOLDER</div></div>';
  } catch(e){ showErr('Cannot reach server — ' + e.message); }
}

function showErr(m){ document.getElementById('mailList').innerHTML=`<div class="err">⚠ ${m}</div>`; }

function renderList(mails){
  const el=document.getElementById('mailList');
  if(!mails.length){el.innerHTML='<div class="info">No messages</div>';return;}
  el.innerHTML=mails.map((m,i)=>`
    <div class="mi ${m.unread?'unread':''} ${m.flagged?'flagged':''} ${m.answered?'answered':''} ${i===0?'active':''}"
         onclick="selectMail(this,${m.uid})" data-uid="${m.uid}">
      <div class="mi-r1"><span class="mi-from">${m.from}</span><span class="mi-time">${m.date}</span></div>
      <div class="mi-subj">${m.subject}</div>
    </div>`).join('');
}

function renderPg(total){
  const pages=Math.ceil(total/<?= ITEMS_PER_PAGE ?>), el=document.getElementById('pgBar');
  if(pages<=1){el.innerHTML='';return;}
  el.innerHTML=Array.from({length:Math.min(pages,10)},(_,i)=>
    `<button class="pgb ${curPage===i+1?'cur':''}" onclick="loadMails(null,${i+1},null)">${i+1}</button>`
  ).join('');
}

// READ
function selectMail(el,uid){
  document.querySelectorAll('.mi').forEach(e=>e.classList.remove('active'));
  el.classList.add('active'); el.classList.remove('unread');
  readMail(uid);
}

async function readMail(uid){
  curUID=uid;
  document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">⚡</div><div>Loading…</div></div>';
  try{
    const r=await fetch(url('read',{uid,folder:curFolder}));
    const m=await r.json();
    if(m.error){ document.getElementById('mailView').innerHTML=`<div class="empty"><div class="empty-ico">⚠️</div><div>${m.error}</div></div>`; return; }
    const atts=m.attachments?.length
      ?`<div class="atts">${m.attachments.map(a=>`<span class="att" title="Attachment">📎 ${a.name}</span>`).join('')}</div>`:'';
    const ccRow=m.cc?`<div class="meta-b"><span class="meta-l">CC</span><span class="meta-v">${m.cc}</span></div><div class="msep"></div>`:'';
    document.getElementById('mailView').innerHTML=`
      <div class="mv-head">
        <div class="mv-subj">${m.subject}</div>
        <div class="meta">
          <div class="meta-b"><span class="meta-l">FROM</span><span class="meta-v">${m.from} &lt;${m.from_email}&gt;</span></div>
          <div class="msep"></div>
          <div class="meta-b"><span class="meta-l">TO</span><span class="meta-v">${m.to}</span></div>
          <div class="msep"></div>
          ${ccRow}
          <div class="meta-b"><span class="meta-l">DATE</span><span class="meta-v">${m.date}</span></div>
        </div>
      </div>
      <div class="mv-actions">
        <button class="ab pri" onclick="openCompose('${esc(m.from_email)}','Re: ${esc(m.subject)}')">↩ Reply</button>
        <button class="ab" onclick="openCompose('','Fwd: ${esc(m.subject)}')">↪ Forward</button>
        <button class="ab" onclick="flagMail(${uid})">⭐ Star</button>
        <button class="ab" onclick="markUnread(${uid})">✉ Unread</button>
        <button class="ab" onclick="openMoveDialog(${uid})">📁 Move</button>
        <button class="ab" onclick="printMail()">🖨 Print</button>
        <button class="ab red" style="margin-left:auto;" onclick="delMail(${uid})">🗑 Delete</button>
      </div>
      <div class="mv-body" id="mvBody">${m.body}</div>
      ${atts}`;
  }catch(e){
    document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">⚠️</div><div>Failed to load: '+e.message+'</div></div>';
  }
}

// ACTIONS
async function delMail(uid){
  if(!confirm('Delete this message?'))return;
  await fetch(url('delete',{uid,folder:curFolder}));
  document.querySelector(`[data-uid="${uid}"]`)?.remove();
  document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">🗑️</div><div>DELETED</div></div>';
  toast('Deleted');
}

async function flagMail(uid){
  await fetch(url('flag',{uid,folder:curFolder,state:1}));
  document.querySelector(`[data-uid="${uid}"]`)?.classList.add('flagged');
  toast('⭐ Starred');
}

async function markUnread(uid){
  await fetch(url('mark_unread',{uid,folder:curFolder}));
  document.querySelector(`[data-uid="${uid}"]`)?.classList.add('unread');
  toast('Marked unread');
}

async function moveMail(uid, dest){
  closeMoveDialog();
  await fetch(url('move',{uid,folder:curFolder,dest}));
  document.querySelector(`[data-uid="${uid}"]`)?.remove();
  document.getElementById('mailView').innerHTML=`<div class="empty"><div class="empty-ico">📁</div><div>MOVED TO ${dest.toUpperCase()}</div></div>`;
  toast(`Moved to ${dest}`);
}

function openMoveDialog(uid){
  curUID=uid;
  const fList=['INBOX','Sent','Drafts','Junk','Trash',...folders.filter(f=>!['INBOX','Sent','Drafts','Junk','Trash'].includes(f))];
  document.getElementById('moveFolders').innerHTML=fList.map(f=>
    `<div class="move-opt" onclick="moveMail(${uid},'${f}')">${f}</div>`).join('');
  document.getElementById('moveDlg').classList.add('open');
}
function closeMoveDialog(){ document.getElementById('moveDlg').classList.remove('open'); }

function printMail(){
  const body=document.getElementById('mvBody')?.innerHTML||'';
  const w=window.open('','_blank');
  w.document.write(`<html><head><title>Print</title></head><body style="font-family:Arial;padding:20px;">${body}</body></html>`);
  w.document.close(); w.print();
}

// COMPOSE
function openCompose(to='',subject=''){
  document.getElementById('cTo').value=to;
  document.getElementById('cSub').value=subject;
  document.getElementById('cCc').value='';
  document.getElementById('cBcc').value='';
  document.getElementById('cBody').innerHTML='';
  insertSig();
  document.getElementById('overlay').classList.add('open');
  setTimeout(()=>document.getElementById(to?'cSub':'cTo').focus(),300);
}
function closeCompose(){ document.getElementById('overlay').classList.remove('open'); }

async function insertSig(){
  try{
    const r=await fetch(url('signature',{}));
    const d=await r.json();
    const body=document.getElementById('cBody');
    // Only insert if empty or only has sig already
    const existing=body.innerHTML;
    if(!existing||existing.startsWith('<br>')){
      body.innerHTML='<br><br>'+d.html;
    }
    updateCharCount();
  }catch(e){}
}

function clearBody(){ document.getElementById('cBody').innerHTML=''; updateCharCount(); }
function fmt(cmd){ document.execCommand(cmd,false,null); }
function insertLink(){
  const u=prompt('URL:','https://');
  if(u) document.execCommand('createLink',false,u);
}
function updateCharCount(){
  const len=document.getElementById('cBody').innerText.length;
  document.getElementById('charCount').textContent=len+' chars';
}

async function sendMail(){
  const to  = document.getElementById('cTo').value.trim();
  const sub  = document.getElementById('cSub').value.trim();
  const cc   = document.getElementById('cCc').value.trim();
  const bcc  = document.getElementById('cBcc').value.trim();
  const body = document.getElementById('cBody').innerHTML;
  if(!to||!sub){ toast('Fill in To and Subject',false); return; }
  const btn=document.getElementById('sendBtn');
  btn.disabled=true; btn.textContent='Sending…';
  const fd=new FormData();
  fd.append('to',to); fd.append('subject',sub); fd.append('body',body); fd.append('cc',cc); fd.append('bcc',bcc);
  try{
    const r=await fetch('?ajax=send',{method:'POST',body:fd});
    const d=await r.json();
    if(d.ok){ closeCompose(); toast('✉ Sent via port '+d.port+'!'); }
    else toast('⚠ '+(d.error||'Send failed'),false);
  }catch(e){ toast('⚠ Network error: '+e.message,false); }
  finally{ btn.disabled=false; btn.textContent='Send ▶'; }
}

async function saveDraft(){
  const to   = document.getElementById('cTo').value.trim();
  const sub  = document.getElementById('cSub').value.trim() || '(no subject)';
  const cc   = document.getElementById('cCc').value.trim();
  const bcc  = document.getElementById('cBcc').value.trim();
  const body = document.getElementById('cBody').innerHTML;
  toast('💾 Saving draft…');
  const fd=new FormData();
  fd.append('to',to); fd.append('subject',sub); fd.append('body',body); fd.append('cc',cc); fd.append('bcc',bcc);
  try{
    const r=await fetch('?ajax=draft',{method:'POST',body:fd});
    const d=await r.json();
    if(d.ok){ toast('💾 Draft saved to Drafts folder'); closeCompose(); }
    else toast('⚠ Draft save failed: '+(d.error||'unknown'),false);
  }catch(e){ toast('⚠ Draft error: '+e.message,false); }
}

// FOLDER / SEARCH
function switchFolder(el){
  document.querySelectorAll('.nav').forEach(n=>n.classList.remove('active'));
  el.classList.add('active');
  const label=el.innerText.replace(/[^\w ]/g,'').trim();
  document.getElementById('folderName').textContent=label.toUpperCase();
  document.getElementById('searchInp').value='';
  loadMails(el.dataset.folder,1,'ALL');
}
function doSearch(s){ curSearch=s; loadMails(null,1,s); }
function debSearch(v){
  clearTimeout(searchTimer);
  searchTimer=setTimeout(()=>{
    const s=v.trim();
    doSearch(s?`OR FROM "${s}" SUBJECT "${s}"` :'ALL');
  },500);
}

// DEBUG
let dbgOpen=false;
async function toggleDebug(){
  const p=document.getElementById('dbgPanel');
  dbgOpen=!dbgOpen; p.classList.toggle('open',dbgOpen);
  if(!dbgOpen)return;
  document.getElementById('dbgOut').innerHTML='<span class="warn">Testing connections…</span>';
  try{
    const r=await fetch(url('debug',{folder:'INBOX'}));
    const d=await r.json();
    let h='';
    for(const[k,v] of Object.entries(d)){
      const s=String(v);
      const c=s.startsWith('OK')||s==='YES'||s.startsWith('235')?'ok':
              s.startsWith('FAIL')||s==='NO'||s.startsWith('5')?'fail':'warn';
      h+=`<div class="dbg-row"><span class="dbg-k">${k}</span><span class="${c}">${s}</span></div>`;
    }
    document.getElementById('dbgOut').innerHTML=h;
  }catch(e){document.getElementById('dbgOut').innerHTML=`<span class="fail">${e.message}</span>`;}
}

// LOGOUT
async function logout(){
  if(!confirm('Log out?'))return;
  await fetch(url('logout',{}));
  window.location.href='../mail-login.php';
}

// TOAST
function toast(msg,ok=true){
  const el=document.createElement('div');
  el.className='toast'+(ok?'':' err');
  el.textContent=msg;
  document.body.appendChild(el);
  setTimeout(()=>el.remove(),3500);
}

// HELPERS
function url(ajax,params){
  const p=new URLSearchParams({ajax,...params});
  return '?'+p.toString();
}
const esc=s=>String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'&quot;').replace(/</g,'&lt;');

// Close overlay on bg click
document.getElementById('overlay').addEventListener('click',e=>{
  if(e.target===document.getElementById('overlay'))closeCompose();
});
document.getElementById('moveDlg').addEventListener('click',e=>{
  if(e.target===document.getElementById('moveDlg'))closeMoveDialog();
});

// Load folders for move dialog
async function loadFolders(){
  try{
    const r=await fetch(url('folders',{folder:'INBOX'}));
    const d=await r.json();
    if(d.folders) folders=d.folders;
  }catch(e){}
}

// INIT
loadMails('INBOX',1,'ALL');
loadFolders();
</script>
</body>
</html>