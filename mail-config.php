<?php
/**
 * Mail Admin — Configuration
 * Pradip Subedi | Electrical Engineer
 * pradipsubedi1.com.np
 *
 * ⚠ KEEP THIS FILE PRIVATE — never share or commit to git
 */

// ─── MAIL SERVER (from cPanel VBS config) ─────────────────
define('MAIL_HOST',      'mail.pradipsubedi1.com.np');
define('MAIL_PORT',      993);                          // IMAP SSL
define('MAIL_USER',      'info@pradipsubedi1.com.np');
define('MAIL_PASS',      '.!&amp;9UDYUX.(o2+I;');         // ← set your password
define('MAIL_SSL',       true);

// ─── SMTP ─────────────────────────────────────────────────
define('SMTP_PORT_SSL',  465);
define('SMTP_PORT_TLS',  587);

// ─── DISPLAY ──────────────────────────────────────────────
define('MAIL_FROM_NAME', 'Pradip Subedi');
define('ITEMS_PER_PAGE', 20);

// ─── SIGNATURE ────────────────────────────────────────────
define('SIG_NAME',       'Pradip Subedi');
define('SIG_TITLE',      'Electrical Engineer | Power Systems Specialist');
define('SIG_EMAIL',      'info@pradipsubedi1.com.np');
define('SIG_WEBSITE',    'https://pradipsubedi1.com.np');
define('SIG_PHONE',      '+977-9843944252');          // ← update your phone
define('SIG_LOCATION',   'Kathmandu, Nepal');
define('SIG_LINKEDIN',   '#');                          // ← update LinkedIn URL