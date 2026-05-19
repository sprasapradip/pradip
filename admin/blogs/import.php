<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

function createSlug($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_FILES['docx']['tmp_name'])) {
        die("No file uploaded");
    }

    $title = trim($_POST['title'] ?? 'Imported Blog');

    $file = $_FILES['docx']['tmp_name'];

    $outputFile = '../../uploads/import_' . time() . '.html';

    $pandoc = '"C:\\Program Files\\Pandoc\\pandoc.exe"';

    $cmd = $pandoc . " " .
        escapeshellarg($file) .
        " -f docx -t html --extract-media=../../uploads/media -o " .
        escapeshellarg($outputFile);

    exec($cmd, $out, $status);

    if ($status !== 0) {
        die("Import failed. Check Pandoc.");
    }

    $content = file_get_contents($outputFile);

    /* CLEAN HTML */
    $content = preg_replace('/^.*?<body>/is', '', $content);
    $content = preg_replace('/<\/body>.*$/is', '', $content);

    $content = trim($content);

    /* AUTO SEO */
    $meta_title = $title;
    $meta_description = substr(strip_tags($content), 0, 160);
    $keywords = implode(',', array_slice(explode(' ', strip_tags($title)), 0, 6));

    /* SLUG */
    $slug = createSlug($title);

    /* IMAGE (optional first image detection) */
    preg_match('/<img[^>]+src="([^"]+)"/i', $content, $imgMatch);
    $image = '';

    if (!empty($imgMatch[1])) {
        $image = basename($imgMatch[1]);
    }

    /* INSERT BLOG */
    $stmt = $conn->prepare("
        INSERT INTO blogs
        (title, slug, content, meta_title, meta_description, keywords, image, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'published')
    ");

    $stmt->bind_param(
        "sssssss",
        $title,
        $slug,
        $content,
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

<h1>Import Word Document</h1>

<form method="POST" enctype="multipart/form-data" class="card">

    <label>Blog Title</label>
    <input type="text" name="title" required>

    <br><br>

    <label>Upload DOCX File</label>
    <input type="file" name="docx" accept=".docx" required>

    <br><br>

    <button class="btn">Import Blog</button>

</form>

</section>

<?php include '../layout/footer.php'; ?>