<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   FETCH DATA
========================= */
$profile = $conn->query("SELECT * FROM profile LIMIT 1")->fetch_assoc();
$exp = $conn->query("SELECT * FROM experience ORDER BY id DESC");
$edu = $conn->query("SELECT * FROM education ORDER BY id DESC");

/* =========================
   DOMPDF SETUP (IMPORTANT FIX)
========================= */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);

/* =========================
   SIMPLE CLEAN HTML (ATS SAFE)
========================= */

$html = "
<h1>{$profile['name']}</h1>
<h3>{$profile['title']}</h3>

<p>{$profile['about']}</p>

<hr>

<h2>Contact</h2>
<p>
Phone: {$profile['phone']}<br>
Email: {$profile['email']}<br>
Website: {$profile['website']}<br>
GitHub: {$profile['github']}
</p>

<hr>

<h2>Experience</h2>
";

while($e = $exp->fetch_assoc()){
    $html .= "
    <p>
    <b>{$e['title']}</b><br>
    {$e['company']}<br>
    {$e['description']}
    </p>
    ";
}

$html .= "<hr><h2>Education</h2>";

while($ed = $edu->fetch_assoc()){
    $html .= "
    <p>
    <b>{$ed['degree']}</b><br>
    {$ed['institution']}<br>
    {$ed['description']}
    </p>
    ";
}

/* =========================
   LOAD PDF
========================= */
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Pradip-CV.pdf", ["Attachment" => true]);
exit;