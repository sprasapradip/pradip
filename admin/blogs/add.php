<?php
define('APP_INIT', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config/guard.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO blog (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include __DIR__ . '/../../layout/header.php';
?>

<section class="admin-page">

<h1>Add Blog</h1>

<form method="POST">

    <input type="text" name="title" placeholder="Blog Title" required>

    <textarea name="content"  id="editor" placeholder="Blog Content" rows="8" required></textarea>

    <button type="submit" class="btn">Save</button>

</form>

</section>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<script>
    CKEDITOR.replace('editor', {
        height: 300,
        removeButtons: '',
    });
</script>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
