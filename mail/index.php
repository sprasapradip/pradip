<?php
session_start();




error_reporting(E_ALL);
ini_set('display_errors', 0);

set_error_handler(function($severity, $message, $file, $line){
    throw new ErrorException($message, 0, $severity, $file, $line);
});







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

// ─── IMAP CONNECT ─────────────────────────────────────────
// Tries multiple common folder name patterns automatically
function imapConn(string $folder = 'INBOX') {

    $base = MAIL_HOST . ':993/imap/ssl';

    $aliases = [
        'INBOX'  => ['INBOX'],
        'Sent'   => ['Sent', 'Sent Messages', 'INBOX.Sent', 'Sent Items'],
        'Drafts' => ['Drafts', 'INBOX.Drafts'],
        'Junk'   => ['Junk', 'Spam', 'INBOX.Spam'],
        'Trash'  => ['Trash', 'Deleted Messages', 'INBOX.Trash']
    ];

    $try = $aliases[$folder] ?? [$folder];

    foreach ($try as $f) {

        try {

            $mailbox = '{' . $base . '}' . $f;

            $imap = @imap_open(
                $mailbox,
                MAIL_USER,
                MAIL_PASS,
                OP_HALFOPEN,
                1
            );

            if ($imap) {
                return $imap;
            }

        } catch(Throwable $e) {

        }
    }

    return false;
}

// Find the real name of a folder on the server
function resolveFolder(string $folder): string {
    $base = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}';
    $aliases = [
        'Sent'   => ['sent', 'sent messages', 'inbox.sent', 'sent items'],
        'Drafts' => ['drafts', 'inbox.drafts', 'draft'],
        'Junk'   => ['junk', 'spam', 'junk e-mail', 'inbox.junk', 'junk mail', 'bulk mail'],
        'Trash'  => ['trash', 'deleted', 'deleted items', 'inbox.trash', 'deleted messages'],
    ];
    if (!isset($aliases[$folder])) return $folder;
    $c = @imap_open($base . 'INBOX', MAIL_USER, MAIL_PASS);
    if (!$c) return $folder;
    $list = @imap_list($c, $base, '*') ?: [];
    imap_close($c);
    foreach ($list as $f) {
        $name = str_replace($base, '', $f);
        if (in_array(strtolower($name), $aliases[$folder])) return $name;
    }
    return $folder;
}

// ─── DECODE ───────────────────────────────────────────────
function dec(string $s): string {
    $out = '';
    foreach (imap_mime_header_decode($s) as $p) $out .= $p->text;
    return htmlspecialchars($out, ENT_QUOTES | ENT_HTML5);
}

// ─── BODY ─────────────────────────────────────────────────
function getBody($c, int $n, $st, string $pn = ''): string {
    if (!isset($st->parts)) {
        return decPart(imap_fetchbody($c, $n, $pn ?: '1'), $st->encoding);
    }
    $html = $plain = '';
    foreach ($st->parts as $i => $p) {
        $num = ($pn ? "$pn." : '') . ($i + 1);
        if ($p->type === 0) {
            $b = decPart(imap_fetchbody($c, $n, $num), $p->encoding);
            strtoupper($p->subtype) === 'HTML' ? $html = $b : $plain = $b;
        } elseif (isset($p->parts)) {
            $s = getBody($c, $n, $p, $num);
            if ($s) $html = $s;
        }
    }
    return $html ?: nl2br(htmlspecialchars($plain));
}
function decPart(string $b, int $e): string {
    if ($e === 3) return base64_decode($b);
    if ($e === 4) return quoted_printable_decode($b);
    return $b;
}

// ─── ATTACHMENTS ──────────────────────────────────────────
function getAtts($st): array {
    $a = [];
    if (!isset($st->parts)) return $a;
    foreach ($st->parts as $i => $p) {
        if (isset($p->disposition) && strtolower($p->disposition) === 'attachment') {
            $name = '';
            foreach (array_merge($p->dparameters ?? [], $p->parameters ?? []) as $x)
                if (in_array(strtolower($x->attribute), ['filename','name'])) $name = $x->value;
            $a[] = ['name' => $name ?: "file_$i", 'part' => $i + 1];
        }
    }
    return $a;
}

// ─── SMTP SEND ────────────────────────────────────────────
function sendSMTP(string $to, string $subject, string $body, string $cc = '', string $bcc = ''): array {
    $h = MAIL_HOST; $u = MAIL_USER; $p = MAIL_PASS;
    $full = $body . sigHTML();
    $raw  = "Date: " . date('r') . "\r\n"
          . "From: " . SIG_NAME . " <$u>\r\n"
          . "To: $to\r\n"
          . ($cc  ? "Cc: $cc\r\n"  : '')
          . "Reply-To: $u\r\n"
          . "Message-ID: <" . uniqid('', true) . "@$h>\r\n"
          . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
          . "MIME-Version: 1.0\r\n"
          . "Content-Type: text/html; charset=UTF-8\r\n"
          . "Content-Transfer-Encoding: base64\r\n\r\n"
          . chunk_split(base64_encode($full));

    $ctx = stream_context_create(['ssl' => [
        'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true
    ]]);

    $result = ['ok' => false, 'error' => 'All SMTP ports failed'];
    foreach ([[465, true],[587, false]] as [$port, $ssl]) {
        $r = doSMTP($h, $port, $ssl, $u, $p, $to, $cc, $bcc, $raw, $ctx);
        if ($r['ok']) {
            saveSentViaIMAP($raw);
            return $r;
        }
        $result = $r;
    }
    return $result;
}

// Append sent mail to Sent folder via IMAP
function saveSentViaIMAP(string $raw): void {
    $base = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}';
    $c = @imap_open($base . 'INBOX', MAIL_USER, MAIL_PASS);
    if (!$c) return;
    $list = @imap_list($c, $base, '*') ?: [];
    imap_close($c);
    $sentFolder = 'Sent';
    foreach ($list as $f) {
        $name = str_replace($base, '', $f);
        if (preg_match('/sent/i', $name)) { $sentFolder = $name; break; }
    }
    $c2 = @imap_open($base . $sentFolder, MAIL_USER, MAIL_PASS);
    if ($c2) {
        @imap_append($c2, $base . $sentFolder, $raw, '\\Seen');
        imap_close($c2);
    }
}

function doSMTP($host, $port, $ssl, $u, $p, $to, $cc, $bcc, $raw, $ctx): array {
    try {
        $sock = $ssl
            ? @stream_socket_client("ssl://$host:$port", $en, $er, 15, STREAM_CLIENT_CONNECT, $ctx)
            : @stream_socket_client("tcp://$host:$port", $en, $er, 15);
        if (!$sock) return ['ok'=>false,'error'=>"Port $port connect failed: $er"];

        $rd = function() use ($sock) {
            $o=''; while ($l = fgets($sock, 512)) { $o .= $l; if (isset($l[3]) && $l[3] === ' ') break; } return $o;
        };
        $cm = function($c) use ($sock, $rd) { fwrite($sock, "$c\r\n"); return $rd(); };

        $rd(); // greeting
        $cm('EHLO ' . (gethostname() ?: 'localhost'));
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
            return ['ok'=>false,'error'=>"Auth failed on port $port: " . trim($auth)];
        }
        $cm("MAIL FROM:<$u>");
        foreach (array_filter(array_map('trim', explode(',', "$to,$cc,$bcc"))) as $r)
            $cm("RCPT TO:<$r>");
        $cm('DATA');
        fwrite($sock, "$raw\r\n.\r\n");
        $resp = $rd();
        $cm('QUIT');
        fclose($sock);
        return str_starts_with(trim($resp), '250')
            ? ['ok'=>true,'port'=>$port]
            : ['ok'=>false,'error'=>"Port $port: " . trim($resp)];
    } catch (\Throwable $e) {
        return ['ok'=>false,'error'=>$e->getMessage()];
    }
}

// ─── DRAFT SAVE ───────────────────────────────────────────
function saveDraftViaIMAP(string $to, string $subject, string $body, string $cc = ''): array {
    $base = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}';
    $u = MAIL_USER;

    // Find Drafts folder
    $c = @imap_open($base . 'INBOX', $u, MAIL_PASS);
    if (!$c) return ['ok'=>false,'error'=>'IMAP connect failed: ' . imap_last_error()];
    $list = @imap_list($c, $base, '*') ?: [];
    imap_close($c);

    $draftFolder = 'Drafts';
    foreach ($list as $f) {
        $name = str_replace($base, '', $f);
        if (preg_match('/draft/i', $name)) { $draftFolder = $name; break; }
    }

    $raw = "Date: " . date('r') . "\r\n"
         . "From: " . SIG_NAME . " <$u>\r\n"
         . "To: $to\r\n"
         . ($cc ? "Cc: $cc\r\n" : '')
         . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/html; charset=UTF-8\r\n"
         . "Content-Transfer-Encoding: base64\r\n\r\n"
         . chunk_split(base64_encode($body));

    $c2 = @imap_open($base . $draftFolder, $u, MAIL_PASS);
    if (!$c2) {
        // Try creating the folder
        $c3 = @imap_open($base . 'INBOX', $u, MAIL_PASS);
        if ($c3) { @imap_createmailbox($c3, $base . $draftFolder); imap_close($c3); }
        $c2 = @imap_open($base . $draftFolder, $u, MAIL_PASS);
    }
    if (!$c2) return ['ok'=>false,'error'=>'Cannot open Drafts folder: ' . imap_last_error()];

    $ok = @imap_append($c2, $base . $draftFolder, $raw, '\\Draft');
    imap_close($c2);
    return $ok ? ['ok'=>true,'folder'=>$draftFolder] : ['ok'=>false,'error'=>imap_last_error()];
}

// ─── SIGNATURE ────────────────────────────────────────────
function sigHTML(): string {
    [$n,$t,$e,$w,$ph,$loc,$li] = [SIG_NAME,SIG_TITLE,SIG_EMAIL,SIG_WEBSITE,SIG_PHONE,SIG_LOCATION,SIG_LINKEDIN];
    return "<br><br>
<table cellpadding='0' cellspacing='0' style='font-family:Arial,sans-serif;border-left:3px solid #00aadd;background:#0a0f1a;color:#c8dff0;max-width:500px;'>
<tr><td style='padding:14px 18px;'>
<p style='margin:0 0 2px;font-size:18px;font-weight:700;color:#fff;'>$n</p>
<p style='margin:0 0 12px;font-size:11px;color:#00aadd;letter-spacing:2px;text-transform:uppercase;'>&#x26A1; $t</p>
<hr style='border:none;border-top:1px solid #1e3a5f;margin-bottom:10px;'>
<table cellpadding='3' cellspacing='0' style='font-size:12px;width:100%;'>
<tr><td>&#x1F4E7;</td><td><a href='mailto:$e' style='color:#c8dff0;'>$e</a></td><td>&#x1F310;</td><td><a href='$w' style='color:#c8dff0;'>$w</a></td></tr>
<tr><td>&#x1F4DE;</td><td style='color:#c8dff0;'>$ph</td><td>&#x1F4CD;</td><td style='color:#c8dff0;'>$loc</td></tr>
<tr><td>&#x1F517;</td><td colspan='3'><a href='$li' style='color:#c8dff0;'>LinkedIn Profile</a></td></tr>
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
    header('Content-Type: application/json');
    @ini_set('display_errors', 0);
    $ajax   = $_GET['ajax'];
    $folder = $_GET['folder'] ?? 'INBOX';

    if ($ajax === 'logout') { session_destroy(); echo json_encode(['ok'=>true]); exit; }
    if ($ajax === 'signature') { echo json_encode(['html' => sigHTML()]); exit; }

    if ($ajax === 'folders') {
        $base = '{' . MAIL_HOST . ':993/imap/ssl/novalidate-cert}';
        $c = @imap_open($base . 'INBOX', MAIL_USER, MAIL_PASS);
        if (!$c) { echo json_encode(['error'=>imap_last_error()]); exit; }
        $list = @imap_list($c, $base, '*') ?: [];
        imap_close($c);
        echo json_encode(['folders' => array_map(fn($f)=>str_replace($base,'',$f), $list)]);
        exit;
    }

    if ($ajax === 'debug') {
        $h=MAIL_HOST; $u=MAIL_USER; $p=MAIL_PASS;
        $out=['host'=>$h,'user'=>$u,'php'=>PHP_VERSION,
              'imap_ext'=>extension_loaded('imap')?'YES':'NO',
              'openssl'=>extension_loaded('openssl')?'YES':'NO'];
        if (extension_loaded('imap')) {
            $base = "{{$h}:993/imap/ssl/novalidate-cert}";
            $c=@imap_open($base.'INBOX',$u,$p);
            $out['imap_993']=$c?'OK — Connected!':'FAILED: '.imap_last_error();
            if ($c) {
                $fl=@imap_list($c,$base,'*')?:[];
                $out['all_folders']=implode(' | ', array_map(fn($f)=>str_replace($base,'',$f),$fl));
                imap_close($c);
            }
        }
        $ctx=stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]]);
        $s=@stream_socket_client("ssl://$h:465",$en,$er,8,STREAM_CLIENT_CONNECT,$ctx);
        if($s){fgets($s,512);fwrite($s,"EHLO localhost\r\n");while($l=fgets($s,512)){if(isset($l[3])&&$l[3]===' ')break;}
            fwrite($s,"AUTH LOGIN\r\n");fgets($s,512);fwrite($s,base64_encode($u)."\r\n");fgets($s,512);
            fwrite($s,base64_encode($p)."\r\n");$out['smtp_465']='OK';$out['smtp_465_auth']=trim(fgets($s,512));fclose($s);
        } else $out['smtp_465']="FAILED: $er";
        $s2=@stream_socket_client("tcp://$h:587",$en2,$er2,8);
        if($s2){fgets($s2,512);fwrite($s2,"EHLO localhost\r\n");while($l=fgets($s2,512)){if(isset($l[3])&&$l[3]===' ')break;}
            fwrite($s2,"STARTTLS\r\n");fgets($s2,512);@stream_socket_enable_crypto($s2,true,STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fwrite($s2,"EHLO localhost\r\n");while($l=fgets($s2,512)){if(isset($l[3])&&$l[3]===' ')break;}
            fwrite($s2,"AUTH LOGIN\r\n");fgets($s2,512);fwrite($s2,base64_encode($u)."\r\n");fgets($s2,512);
            fwrite($s2,base64_encode($p)."\r\n");$out['smtp_587']='OK';$out['smtp_587_auth']=trim(fgets($s2,512));fclose($s2);
        } else $out['smtp_587']="FAILED: $er2";
        echo json_encode($out,JSON_PRETTY_PRINT); exit;
    }

    // Draft save — no IMAP connection needed here (handled inside function)
    if ($ajax === 'draft') {
        $to   = trim($_POST['to']      ?? '');
        $sub  = trim($_POST['subject'] ?? '(No Subject)');
        $cc   = trim($_POST['cc']      ?? '');
        $body = $_POST['body']         ?? '';
        echo json_encode(saveDraftViaIMAP($to, $sub, $body, $cc));
        exit;
    }

    // Send mail — handled separately from IMAP block
    if ($ajax === 'send') {
        $to   = trim($_POST['to']      ?? '');
        $sub  = trim($_POST['subject'] ?? '');
        $body = $_POST['body']         ?? '';
        $cc   = trim($_POST['cc']      ?? '');
        $bcc  = trim($_POST['bcc']     ?? '');
        if (!$to)  { echo json_encode(['ok'=>false,'error'=>'To field empty']); exit; }
        if (!$sub) { echo json_encode(['ok'=>false,'error'=>'Subject empty']);  exit; }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL))
            { echo json_encode(['ok'=>false,'error'=>"Invalid email: $to"]); exit; }
        echo json_encode(sendSMTP($to, $sub, $body, $cc, $bcc));
        exit;
    }

    // All remaining AJAX calls need an IMAP connection
    $c = imapConn($folder);
    if (!$c) {
        $err = imap_last_error() ?: 'Check host/credentials';
        echo json_encode(['error' => "Cannot connect to '$folder': $err"]);
        exit;
    }

    switch ($ajax) {
        case 'list':
            $page   = max(1,(int)($_GET['page']??1));
            $search = trim($_GET['search']??'')?:'ALL';
            $total  = imap_num_msg($c);
            $info   = imap_mailboxmsginfo($c);
            if (!$total){ echo json_encode(['mails'=>[],'total'=>0,'unread'=>(int)($info->Unread??0)]); break; }
            $uids   = @imap_search($c,$search,SE_UID)?:[];
            $uids   = array_reverse($uids);
            $found  = count($uids);
            $paged  = array_slice($uids,($page-1)*ITEMS_PER_PAGE,ITEMS_PER_PAGE);
            $mails  = [];
            foreach ($paged as $uid) {
                $no   = imap_msgno($c,$uid);
                $h    = imap_headerinfo($c,$no);
                $overview = imap_fetch_overview($c, $no, 0);
                $ov = $overview[0] ?? null;
                $from = $h->from[0]??null;
                $nm   = $from?(imap_utf8($from->personal??'')?:($from->mailbox.'@'.$from->host)):'Unknown';
                $mails[]=['uid'=>$uid,'from'=>htmlspecialchars($nm),'email'=>$from?($from->mailbox.'@'.$from->host):'',
                    'subject'=>dec($h->subject??'(no subject)'),'date'=>date('d M, H:i',strtotime($h->date??'now')),
                    
                    'unread'=>!($ov->seen ?? false),
                    'flagged'=>($ov->flagged ?? false),
                    'answered'=>($ov->answered ?? false)
                    ];
            }
            echo json_encode(['mails'=>$mails,'total'=>$found,'unread'=>(int)($info->Unread??0)]);
            break;

        case 'read':
            $uid  = (int)($_GET['uid']??0);
            $no   = imap_msgno($c,$uid);
            $h    = imap_headerinfo($c,$no);
            $st   = imap_fetchstructure($c,$no);
            imap_setflag_full($c,(string)$uid,'\\Seen',ST_UID);
            $from = $h->from[0]??null; $to=$h->to[0]??null;
            $cc   = isset($h->cc)?array_map(fn($x)=>$x->mailbox.'@'.$x->host,$h->cc):[];
            echo json_encode(['uid'=>$uid,'subject'=>dec($h->subject??'(no subject)'),
                'from'=>htmlspecialchars($from?imap_utf8($from->personal??''):'Unknown'),
                'from_email'=>$from?($from->mailbox.'@'.$from->host):'','to'=>$to?($to->mailbox.'@'.$to->host):MAIL_USER,
                'cc'=>implode(', ',$cc),'date'=>date('l, d F Y \a\t H:i',strtotime($h->date??'now')),
                'body'=>getBody($c,$no,$st),'attachments'=>getAtts($st)]);
            break;

        case 'delete':
            $uid=(int)($_GET['uid']??0);
            imap_delete($c,(string)$uid,FT_UID);
            echo json_encode(['ok'=>imap_expunge($c)]);
            break;

        case 'flag':
            $uid=(int)($_GET['uid']??0); $on=($_GET['state']??'1')==='1';
            $on?imap_setflag_full($c,(string)$uid,'\\Flagged',ST_UID):imap_clearflag_full($c,(string)$uid,'\\Flagged',ST_UID);
            echo json_encode(['ok'=>true]);
            break;

        case 'mark_read':
            $uid=(int)($_GET['uid']??0);
            imap_setflag_full($c,(string)$uid,'\\Seen',ST_UID);
            echo json_encode(['ok'=>true]);
            break;

        case 'mark_unread':
            $uid=(int)($_GET['uid']??0);
            imap_clearflag_full($c,(string)$uid,'\\Seen',ST_UID);
            echo json_encode(['ok'=>true]);
            break;

        case 'move':
            $uid=(int)($_GET['uid']??0);
            $dest = resolveFolder($_GET['dest']??'Trash');
            imap_mail_move($c,(string)$uid,$dest,CP_UID);
            echo json_encode(['ok'=>imap_expunge($c)]);
            break;

        case 'count':
            $info=imap_mailboxmsginfo($c);
            echo json_encode(['total'=>(int)imap_num_msg($c),'unread'=>(int)($info->Unread??0)]);
            break;
    }
    imap_close($c);
    exit;
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
    .layout{display:grid;grid-template-columns:200px 290px 1fr;height:calc(100vh - 58px);}
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
    .err{color:var(--red);padding:14px;font-family:'Share Tech Mono',monospace;font-size:10px;text-align:center;line-height:1.6;}
    .pg{display:flex;gap:4px;padding:6px 10px;border-top:1px solid var(--border);background:var(--panel);flex-wrap:wrap;}
    .pgb{background:rgba(26,51,84,.3);border:1px solid var(--border);border-radius:3px;padding:3px 8px;
      color:var(--muted);cursor:pointer;font-family:'Share Tech Mono',monospace;font-size:8px;}
    .pgb.cur{border-color:var(--accent);color:var(--accent);background:rgba(0,200,240,.12);}
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
    .mv-body img{max-width:100%;} .mv-body a{color:var(--accent);}
    .atts{display:flex;gap:6px;padding:8px 18px;border-top:1px solid var(--border);flex-wrap:wrap;background:var(--panel2);}
    .att{background:rgba(0,200,240,.07);border:1px solid var(--border);border-radius:4px;padding:4px 10px;
      font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);cursor:pointer;}
    .att:hover{border-color:var(--accent);color:var(--accent);}
    .empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:10px;
      color:var(--muted);font-family:'Share Tech Mono',monospace;font-size:10px;}
    .empty-ico{font-size:42px;opacity:.12;}
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
      padding:8px 24px;color:#fff;font-family:'Rajdhani',sans-serif;font-size:14px;font-weight:700;
      letter-spacing:1px;text-transform:uppercase;cursor:pointer;transition:all .2s;}
    .send-btn:hover{box-shadow:var(--glow2);}
    .send-btn:disabled{opacity:.5;cursor:not-allowed;}
    .draft-btn{background:rgba(255,149,0,.1);border:1px solid rgba(255,149,0,.4);border-radius:5px;
      padding:8px 16px;color:var(--accent2);font-family:'Rajdhani',sans-serif;font-size:13px;font-weight:700;
      letter-spacing:1px;text-transform:uppercase;cursor:pointer;transition:all .2s;}
    .draft-btn:hover{background:rgba(255,149,0,.2);}
    .char-count{margin-left:auto;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);}
    .dbg-panel{position:fixed;top:64px;right:16px;background:var(--panel);border:1px solid var(--accent);
      border-radius:8px;padding:16px;z-index:400;width:480px;max-height:75vh;overflow-y:auto;
      font-family:'Share Tech Mono',monospace;font-size:10px;box-shadow:0 0 24px rgba(0,200,240,.2);display:none;}
    .dbg-panel.open{display:block;}
    .dbg-row{display:flex;gap:8px;padding:3px 0;border-bottom:1px solid rgba(26,51,84,.3);}
    .dbg-k{color:var(--muted);min-width:150px;flex-shrink:0;}
    .ok{color:var(--green);} .fail{color:var(--red);} .warn{color:var(--accent2);}
    .move-dlg{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
      background:var(--panel);border:1px solid var(--accent);border-radius:8px;padding:20px;z-index:300;
      min-width:280px;display:none;}
    .move-dlg.open{display:block;}
    .move-dlg h3{font-family:'Rajdhani',sans-serif;color:var(--accent);margin-bottom:12px;letter-spacing:1px;}
    .move-opt{padding:8px 12px;cursor:pointer;border-radius:4px;font-size:13px;transition:background .15s;}
    .move-opt:hover{background:rgba(0,200,240,.1);color:var(--accent);}
    .toast{position:fixed;bottom:20px;right:20px;background:var(--panel);border:1px solid var(--green);
      border-radius:6px;padding:9px 16px;font-family:'Share Tech Mono',monospace;font-size:10px;
      color:var(--green);z-index:500;animation:fadeUp .3s;box-shadow:0 0 12px rgba(0,255,136,.25);}
    .toast.err{border-color:var(--red);color:var(--red);}
    .toast.warn{border-color:var(--accent2);color:var(--accent2);}
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

<div class="topbar">
  <div class="brand">
    <div class="brand-ico">&#x26A1;</div>
    <div class="brand-name"><h1>Mail Admin</h1><small>pradipsubedi1.com.np</small></div>
  </div>
  <div class="topbar-r">
    <div class="status"><div class="dot"></div>SSL LIVE</div>
    <span class="clock" id="clock">--:--:--</span>
    <button class="tbtn dbg" onclick="toggleDebug()">&#x1F527; Debug</button>
    <div class="acct"><?= MAIL_USER ?></div>
    <button class="tbtn compose" onclick="openCompose()">&#x26A1; Compose</button>
    <button class="tbtn danger" onclick="logout()">&#x23FB; Logout</button>
  </div>
</div>

<div class="dbg-panel" id="dbgPanel">
  <div style="color:var(--accent);font-size:12px;font-weight:700;margin-bottom:10px;letter-spacing:1px;">&#x1F527; SERVER DEBUG</div>
  <div id="dbgOut"><span class="warn">Click Debug to run…</span></div>
  <button class="tbtn" onclick="toggleDebug()" style="margin-top:10px;width:100%;justify-content:center;">&#x2715; Close</button>
</div>

<div class="move-dlg" id="moveDlg">
  <h3>&#x1F4C1; Move To Folder</h3>
  <div id="moveFolders"></div>
  <button class="tbtn" onclick="closeMoveDialog()" style="margin-top:10px;width:100%;justify-content:center;">Cancel</button>
</div>

<div class="layout">
  <div class="sidebar">
    <div class="sb-section">FOLDERS</div>
    <div class="nav active" data-folder="INBOX"  onclick="switchFolder(this)"><span class="nav-ico">&#x1F4E5;</span>Inbox<span class="nbadge" id="badge-unread">…</span></div>
    <div class="nav"        data-folder="Sent"   onclick="switchFolder(this)"><span class="nav-ico">&#x1F4E4;</span>Sent</div>
    <div class="nav"        data-folder="Drafts" onclick="switchFolder(this)"><span class="nav-ico">&#x1F4DD;</span>Drafts</div>
    <div class="nav"        data-folder="Junk"   onclick="switchFolder(this)"><span class="nav-ico">&#x1F6AB;</span>Spam</div>
    <div class="nav"        data-folder="Trash"  onclick="switchFolder(this)"><span class="nav-ico">&#x1F5D1;</span>Trash</div>
    <div class="sb-section">QUICK FILTER</div>
    <div class="nav" onclick="doSearch('UNSEEN')" ><span class="nav-ico" style="color:var(--accent)">&#x25C6;</span>Unread</div>
    <div class="nav" onclick="doSearch('FLAGGED')"><span class="nav-ico" style="color:var(--accent2)">&#x25C6;</span>Starred</div>
    <div class="nav" onclick="doSearch('ANSWERED')"><span class="nav-ico" style="color:var(--green)">&#x25C6;</span>Replied</div>
    <div class="nav" onclick="doSearch('SUBJECT &quot;tender&quot;')"><span class="nav-ico" style="color:var(--yellow)">&#x25C6;</span>Tenders</div>
    <div class="nav" onclick="doSearch('SUBJECT &quot;project&quot;')"><span class="nav-ico" style="color:var(--accent)">&#x25C6;</span>Projects</div>
    <div class="nav" onclick="doSearch('SUBJECT &quot;invoice&quot;')"><span class="nav-ico" style="color:var(--accent2)">&#x25C6;</span>Invoices</div>
    <div class="sb-user">
      <div class="sb-user-name"><?= SIG_NAME ?></div>
      <div class="sb-user-email"><?= MAIL_USER ?></div>
    </div>
    <div class="storage">
      <div class="st-lbl"><span>STORAGE</span><span style="color:var(--accent)">cPanel</span></div>
      <div class="st-bar"><div class="st-fill"></div></div>
    </div>
  </div>

  <div class="ml">
    <div class="ml-head">
      <span class="ml-title" id="folderName">INBOX</span>
      <span class="ml-count" id="mlCount">…</span>
    </div>
    <div class="ml-toolbar">
      <input class="search-inp" id="searchInp" placeholder="&#x1F50D; Search…" oninput="debSearch(this.value)"/>
      <button class="sm-btn" onclick="doSearch('UNSEEN')" title="Unread">&#x1F535;</button>
      <button class="sm-btn" onclick="doSearch('FLAGGED')" title="Starred">&#x2B50;</button>
      <button class="sm-btn" onclick="loadMails(null,1,'ALL');document.getElementById('searchInp').value=''" title="Clear">&#x2715;</button>
    </div>
    <div class="mails" id="mailList"><div class="info">&#x26A1; Connecting…</div></div>
    <div class="pg" id="pgBar"></div>
  </div>

  <div class="mv" id="mailView">
    <div class="empty"><div class="empty-ico">&#x26A1;</div><div>SELECT A MESSAGE TO READ</div></div>
  </div>
</div>

<div class="overlay" id="overlay">
  <div class="compose">
    <div class="c-head">
      <span class="c-title">&#x26A1; New Message</span>
      <button class="x-btn" onclick="closeCompose()">&#x2715;</button>
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
      <button class="tool-btn" onclick="insertLink()">&#x1F517;</button>
      <button class="tool-btn" onclick="insertSig()">&#x26A1; Sig</button>
      <button class="tool-btn" onclick="clearBody()">&#x1F5D1; Clear</button>
    </div>
    <div class="c-body" id="cBody" contenteditable="true" oninput="updateCharCount()"></div>
    <div class="c-foot">
      <button class="send-btn" id="sendBtn" onclick="sendMail()">Send &#x25B6;</button>
      <button class="draft-btn" onclick="saveDraft()">&#x1F4BE; Save Draft</button>
      <span class="char-count" id="charCount">0 chars</span>
      <button class="tbtn" style="margin-left:auto;" onclick="closeCompose()">Cancel</button>
    </div>
  </div>
</div>

<script>
let curFolder='INBOX', curPage=1, curSearch='ALL', curUID=null, searchTimer, folders=[];

setInterval(()=>{
  const n=new Date();
  document.getElementById('clock').textContent=n.toLocaleTimeString('en-GB',{hour12:false});
},1000);

async function loadMails(folder, page, search) {
  curFolder = folder ?? curFolder;
  curPage   = page   ?? 1;
  curSearch = search ?? 'ALL';
  document.getElementById('mailList').innerHTML='<div class="info">&#x26A1; Loading…</div>';
  document.getElementById('mlCount').textContent='…';
  try {
    const r = await fetch(url('list',{folder:curFolder,page:curPage,search:curSearch}));
    const d = await r.json();
    if (d.error){ showErr(d.error); return; }
    document.getElementById('mlCount').textContent=`${d.total} · ${d.unread} unread`;
    if (curFolder==='INBOX') document.getElementById('badge-unread').textContent=d.unread||'0';
    renderList(d.mails);
    renderPg(d.total);
    if (d.mails.length) readMail(d.mails[0].uid);
    else document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">&#x1F4ED;</div><div>FOLDER IS EMPTY</div></div>';
  } catch(e){ showErr('Cannot reach server — check IMAP config<br><small>'+e.message+'</small>'); }
}

function showErr(m){ document.getElementById('mailList').innerHTML=`<div class="err">&#x26A0; ${m}</div>`; }

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
  const pages=Math.ceil(total/20), el=document.getElementById('pgBar');
  if(pages<=1){el.innerHTML='';return;}
  el.innerHTML=Array.from({length:Math.min(pages,10)},(_,i)=>
    `<button class="pgb ${curPage===i+1?'cur':''}" onclick="loadMails(null,${i+1},null)">${i+1}</button>`
  ).join('');
}

function selectMail(el,uid){
  document.querySelectorAll('.mi').forEach(e=>e.classList.remove('active'));
  el.classList.add('active'); el.classList.remove('unread');
  readMail(uid);
}

async function readMail(uid){
  curUID=uid;
  document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">&#x26A1;</div><div>Loading…</div></div>';
  try{
    const r=await fetch(url('read',{uid,folder:curFolder}));
    const m=await r.json();
    if(m.error){
      document.getElementById('mailView').innerHTML=`<div class="empty"><div class="empty-ico">&#x26A0;&#xFE0F;</div><div>${m.error}</div></div>`;
      return;
    }
    const atts=m.attachments?.length
      ?`<div class="atts">${m.attachments.map(a=>`<span class="att">&#x1F4CE; ${a.name}</span>`).join('')}</div>`:''
    const ccRow=m.cc?`<div class="meta-b"><span class="meta-l">CC</span><span class="meta-v">${m.cc}</span></div><div class="msep"></div>`:''
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
        <button class="ab pri" onclick="openCompose('${esc(m.from_email)}','Re: ${esc(m.subject)}')">&#x21A9; Reply</button>
        <button class="ab" onclick="openCompose('','Fwd: ${esc(m.subject)}')">&#x21AA; Forward</button>
        <button class="ab" onclick="flagMail(${uid})">&#x2B50; Star</button>
        <button class="ab" onclick="markUnread(${uid})">&#x2709; Unread</button>
        <button class="ab" onclick="openMoveDialog(${uid})">&#x1F4C1; Move</button>
        <button class="ab" onclick="printMail()">&#x1F5A8; Print</button>
        <button class="ab red" style="margin-left:auto;" onclick="delMail(${uid})">&#x1F5D1; Delete</button>
      </div>
      <div class="mv-body" id="mvBody">${m.body}</div>
      ${atts}`;
  }catch(e){
    document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">&#x26A0;&#xFE0F;</div><div>Failed to load message</div></div>';
  }
}

async function delMail(uid){
  if(!confirm('Delete this message?'))return;
  await fetch(url('delete',{uid,folder:curFolder}));
  document.querySelector(`[data-uid="${uid}"]`)?.remove();
  document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">&#x1F5D1;&#xFE0F;</div><div>DELETED</div></div>';
  toast('Deleted');
}

async function flagMail(uid){
  await fetch(url('flag',{uid,folder:curFolder,state:1}));
  document.querySelector(`[data-uid="${uid}"]`)?.classList.add('flagged');
  toast('&#x2B50; Starred');
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
  document.getElementById('mailView').innerHTML=`<div class="empty"><div class="empty-ico">&#x1F4C1;</div><div>MOVED TO ${dest.toUpperCase()}</div></div>`;
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
    document.getElementById('cBody').innerHTML='<br><br>'+d.html;
    updateCharCount();
  }catch(e){}
}

function clearBody(){ document.getElementById('cBody').innerHTML=''; updateCharCount(); }
function fmt(cmd){ document.execCommand(cmd,false,null); }
function insertLink(){ const u=prompt('URL:','https://'); if(u) document.execCommand('createLink',false,u); }
function updateCharCount(){
  const len=document.getElementById('cBody').innerText.length;
  document.getElementById('charCount').textContent=len+' chars';
}

async function sendMail(){
  const to=document.getElementById('cTo').value.trim();
  const sub=document.getElementById('cSub').value.trim();
  const cc=document.getElementById('cCc').value.trim();
  const bcc=document.getElementById('cBcc').value.trim();
  const body=document.getElementById('cBody').innerHTML;
  if(!to||!sub){toast('Fill in To and Subject','err');return;}
  const btn=document.getElementById('sendBtn');
  btn.disabled=true; btn.textContent='Sending…';
  const fd=new FormData();
  fd.append('to',to);fd.append('subject',sub);fd.append('body',body);fd.append('cc',cc);fd.append('bcc',bcc);
  try{
    const r=await fetch('?ajax=send',{method:'POST',body:fd});
    const d=await r.json();
    if(d.ok){
      closeCompose();
      toast('&#x2709; Sent via port '+d.port+'!');
      if(curFolder==='Sent') setTimeout(()=>loadMails('Sent',1,'ALL'),1500);
    } else {
      toast('&#x26A0; '+(d.error||'Send failed'),'err');
    }
  }catch(e){toast('&#x26A0; Network error: '+e.message,'err');}
  finally{btn.disabled=false;btn.textContent='Send \u25B6';}
}

async function saveDraft(){
  const to=document.getElementById('cTo').value.trim();
  const sub=document.getElementById('cSub').value.trim()||'(No Subject)';
  const cc=document.getElementById('cCc').value.trim();
  const body=document.getElementById('cBody').innerHTML;
  toast('&#x1F4BE; Saving draft…','warn');
  const fd=new FormData();
  fd.append('to',to);fd.append('subject',sub);fd.append('body',body);fd.append('cc',cc);
  try{
    const r=await fetch('?ajax=draft',{method:'POST',body:fd});
    const d=await r.json();
    if(d.ok){
      toast('&#x1F4BE; Draft saved to '+d.folder);
      closeCompose();
      if(curFolder==='Drafts') setTimeout(()=>loadMails('Drafts',1,'ALL'),1000);
    } else {
      toast('&#x26A0; Draft failed: '+(d.error||'Unknown'),'err');
    }
  }catch(e){toast('&#x26A0; Network error','err');}
}

function switchFolder(el){
  document.querySelectorAll('.nav').forEach(n=>n.classList.remove('active'));
  el.classList.add('active');
  const txt=el.innerText.replace(/\d+/g,'').trim();
  document.getElementById('folderName').textContent=txt.toUpperCase();
  document.getElementById('searchInp').value='';
  document.getElementById('mailView').innerHTML='<div class="empty"><div class="empty-ico">&#x26A1;</div><div>Loading…</div></div>';
  loadMails(el.dataset.folder,1,'ALL');
}
function doSearch(s){ curSearch=s; loadMails(null,1,s); }
function debSearch(v){
  clearTimeout(searchTimer);
  searchTimer=setTimeout(()=>{
    const s=v.trim();
    doSearch(s ? `TEXT "${s}"` : 'ALL');
  },500);
}

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
      const c=s.startsWith('OK')||s==='YES'||s.startsWith('235')?'ok':s.startsWith('FAIL')||s==='NO'||s.startsWith('5')?'fail':'warn';
      h+=`<div class="dbg-row"><span class="dbg-k">${k}</span><span class="${c}" style="word-break:break-all;">${v}</span></div>`;
    }
    document.getElementById('dbgOut').innerHTML=h;
  }catch(e){document.getElementById('dbgOut').innerHTML=`<span class="fail">${e.message}</span>`;}
}

async function logout(){
  if(!confirm('Log out?'))return;
  await fetch(url('logout',{}));
  window.location.href='../mail-login.php';
}

function toast(msg,type='ok'){
  const el=document.createElement('div');
  el.className='toast'+(type==='err'?' err':type==='warn'?' warn':'');
  el.innerHTML=msg;
  document.body.appendChild(el);
  setTimeout(()=>el.remove(),3500);
}

function url(ajax,params){
  const p=new URLSearchParams({ajax,...params});
  return '?'+p.toString();
}
const esc=s=>String(s).replace(/'/g,"\\'").replace(/"/g,'&quot;').replace(/</g,'&lt;');

document.getElementById('overlay').addEventListener('click',e=>{
  if(e.target===document.getElementById('overlay'))closeCompose();
});
document.getElementById('moveDlg').addEventListener('click',e=>{
  if(e.target===document.getElementById('moveDlg'))closeMoveDialog();
});

async function loadFolders(){
  try{
    const r=await fetch(url('folders',{folder:'INBOX'}));
    const d=await r.json();
    if(d.folders) folders=d.folders;
  }catch(e){}
}

loadMails('INBOX',1,'ALL');
loadFolders();
</script>
</body>
</html>