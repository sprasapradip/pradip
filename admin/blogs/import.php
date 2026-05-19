<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* =========================
   SLUG
========================= */
function createSlug($text){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
}

/* =========================
   AI CLEAN ENGINE
========================= */
function cleanWordHtml($html)
{
    $html = preg_replace('/class="[^"]*"/i', '', $html);
    $html = preg_replace('/style="[^"]*"/i', '', $html);

    $html = preg_replace('/<p>\s*(&nbsp;)?\s*<\/p>/i', '', $html);
    $html = preg_replace('/<span[^>]*>\s*<\/span>/i', '', $html);

    $html = preg_replace('/\r|\n/', '', $html);
    $html = preg_replace('/\s{2,}/', ' ', $html);

    $html = str_replace(
        ['“','”','‘','’'],
        ['"','"',"'", "'"],
        $html
    );

    return trim($html);
}

/* =========================
   AUTO HEADINGS
========================= */
function autoHeadings($html)
{
    $html = preg_replace('/<p><strong>(.*?)<\/strong><\/p>/i', '<h2>$1</h2>', $html);
    $html = preg_replace('/<p>([A-Z0-9\s]{8,})<\/p>/', '<h2>$1</h2>', $html);
    $html = preg_replace('/<p>(\d+\..*?)<\/p>/', '<h3>$1</h3>', $html);

    return $html;
}

/* =========================
   SUMMARY ENGINE
========================= */
function generateSummary($html)
{
    $text = strip_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);

    return substr($text, 0, 160);
}

/* =========================
   DUPLICATE CHECK
========================= */
function isDuplicate($conn, $title)
{
    $stmt = $conn->prepare("
        SELECT id FROM blogs
        WHERE SOUNDEX(title) = SOUNDEX(?)
        LIMIT 1
    ");

    $stmt->bind_param("s", $title);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}

/* =========================
   FEATURED IMAGE
========================= */
function getFeaturedImage($html)
{
    preg_match('/<img[^>]+src="([^"]+)"/i', $html, $m);

    return $m[1] ?? 'default.jpg';
}

/* =========================
   MAIN IMPORT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_FILES['file']['tmp_name'])) {
        die("No file uploaded");
    }

    $title = trim($_POST['title']);

    /* check duplicate */
    if (isDuplicate($conn, $title)) {
        die("Duplicate blog detected");
    }

    $time = time();

    $inputFile = $_FILES['file']['tmp_name'];
    $outputFile = "../../uploads/import_$time.html";
    $mediaDir   = "../../uploads/media_$time";

    $pandoc = '"C:\\Program Files\\Pandoc\\pandoc.exe"';

    $cmd = $pandoc . " " .
        escapeshellarg($inputFile) .
        " -f docx -t html --extract-media=" .
        escapeshellarg($mediaDir) .
        " -o " .
        escapeshellarg($outputFile);

    exec($cmd);

    if (!file_exists($outputFile)) {
        die("Conversion failed");
    }

    $html = file_get_contents($outputFile);

    /* remove body */
    $html = preg_replace('/^.*?<body>/is', '', $html);
    $html = preg_replace('/<\/body>.*$/is', '', $html);

    /* move images */
    if (is_dir($mediaDir)) {
        foreach (scandir($mediaDir) as $f) {
            if ($f == '.' || $f == '..') continue;
            rename($mediaDir.'/'.$f, '../../uploads/blogs/'.$f);
        }
    }

    /* replace paths */
    $html = str_replace('media_', 'uploads/blogs', $html);

    /* AI PIPELINE */
    $html = cleanWordHtml($html);
    $html = autoHeadings($html);

    /* SEO */
    $meta_title = $title;
    $meta_description = generateSummary($html);
    $keywords = implode(',', array_slice(explode(' ', strtolower($title)), 0, 6));

    $slug = createSlug($title);
    $image = getFeaturedImage($html);

    /* INSERT */
    $stmt = $conn->prepare("
        INSERT INTO blogs
        (title, slug, content, meta_title, meta_description, keywords, image, status, views)
        VALUES (?,?,?,?,?,?,?, 'published', 0)
    ");

    $stmt->bind_param(
        "sssssss",
        $title,
        $slug,
        $html,
        $meta_title,
        $meta_description,
        $keywords,
        $image
    );

    $stmt->execute();

    header("Location: index.php?import=success");
    exit;
}
?>

<?php include '../layout/header.php'; ?>

<section class="admin-page">

<h1>🚀 AI CMS Import System</h1>

<form method="POST" enctype="multipart/form-data" class="card">

    <label>Blog Title</label>
    <input type="text" name="title" required>

    <br><br>

    <label>Upload DOCX File</label>
    <input type="file" name="file" accept=".docx,.pdf" required>

    <br><br>

    <button class="btn">
        Import AI Blog
    </button>

</form>

</section>

<?php include '../layout/footer.php'; ?>