<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* =========================
   AI WRITER
========================= */
require_once 'includes/ai_writer.php';

/* =========================
   SLUG GENERATOR
========================= */
function createSlug($conn, $string){

    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    $original = $slug;
    $count = 1;

    while(true){

        $check = $conn->prepare("SELECT id FROM blogs WHERE slug=?");
        $check->bind_param("s", $slug);
        $check->execute();

        if($check->get_result()->num_rows == 0){
            break;
        }

        $slug = $original . '-' . $count;
        $count++;
    }

    return $slug;
}

/* =========================
   POST HANDLER
========================= */
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);

    /* AI BUTTON */
    if(isset($_POST['generate_ai'])){

        $content = generateAIArticle($title, "api safe");

        $meta_title = $title . " | Guide";
        $meta_description = substr(strip_tags($content), 0, 160);
        $keywords = strtolower(str_replace(' ', ', ', $title));

    } else {

        $content = trim($_POST['content']);
        $meta_title = trim($_POST['meta_title'] ?? $title);
        $meta_description = trim($_POST['meta_description'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
    }

    $status = $_POST['status'] ?? 'published';
    $featured = isset($_POST['featured']) ? 1 : 0;

    $slug = createSlug($conn, $title);

    $wordCount = str_word_count(strip_tags($content));
    $reading_time = ceil($wordCount / 200) . ' min read';

    /* IMAGE */
    $imageName = '';

    if(!empty($_FILES['image']['name'])){

        $allowed = ['jpg','jpeg','png','webp'];

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){

            $imageName = time().'_'.uniqid().'.'.$ext;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "../../uploads/".$imageName
            );
        }
    }

    /* INSERT */
    $stmt = $conn->prepare("
        INSERT INTO blogs(
            title,
            slug,
            content,
            image,
            meta_title,
            meta_description,
            keywords,
            status,
            featured,
            reading_time
        )
        VALUES(?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "ssssssssss",
        $title,
        $slug,
        $content,
        $imageName,
        $meta_title,
        $meta_description,
        $keywords,
        $status,
        $featured,
        $reading_time
    );

    $stmt->execute();

    header("Location: index.php?success=1");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

<h1>🚀 Create Blog (WordPress Level Editor)</h1>

<form method="POST" enctype="multipart/form-data" id="blogForm">

    <!-- TITLE -->
    <label>Blog Title</label>
    <input type="text" name="title" required>

    <br><br>

    <!-- AI BUTTON -->
    <button type="submit" name="generate_ai" class="btn">
        ✨ Generate AI Blog
    </button>

    <br><br>

    <!-- CONTENT (FIXED) -->
    <input type="hidden" name="content" id="content">
    <textarea id="editor"></textarea>

    <br><br>

    <!-- FEATURE IMAGE -->
    <label>Featured Image</label>
    <input type="file" name="image" accept="image/*">

    <br><br>

    <!-- META -->
    <label>Meta Title</label>
    <input type="text" name="meta_title">

    <br><br>

    <label>Meta Description</label>
    <textarea name="meta_description"></textarea>

    <br><br>

    <label>Keywords</label>
    <input type="text" name="keywords">

    <br><br>

    <!-- STATUS -->
    <label>Status</label>
    <select name="status">
        <option value="published">Published</option>
        <option value="draft">Draft</option>
    </select>

    <br><br>

    <!-- FEATURED -->
    <label>
        <input type="checkbox" name="featured">
        Featured Blog
    </label>

    <br><br>

    <button class="btn">
        Publish Blog
    </button>

</form>

</section>

<!-- =========================
     CKEDITOR 5 WORDPRESS SETUP
========================= -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>

let editorInstance;

ClassicEditor
    .create(document.querySelector('#editor'), {

        toolbar: [
            'heading',
            '|',
            'bold', 'italic', 'underline',
            '|',
            'link',
            'bulletedList', 'numberedList',
            '|',
            'uploadImage',
            'blockQuote',
            'insertTable',
            'mediaEmbed',
            '|',
            'undo', 'redo'
        ],

        simpleUpload: {
            uploadUrl: '/admin/blog/upload.php'
        }

    })
    .then(editor => {
        editorInstance = editor;
    })
    .catch(error => console.error(error));

/* IMPORTANT FIX */
document.getElementById("blogForm").addEventListener("submit", function () {
    document.getElementById("content").value = editorInstance.getData();
});

</script>

<?php include '../layout/footer.php'; ?>