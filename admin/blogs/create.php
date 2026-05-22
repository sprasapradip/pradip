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
   API KEY (optional)
========================= */
$apiKey = "api safe";

/* =========================
   POST HANDLER
========================= */
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);

    /* =========================
       AI GENERATION BUTTON
    ========================== */
    if(isset($_POST['generate_ai'])){

        $content = generateAIArticle($title, $apiKey);

        $meta_title = $title . " | Complete Guide";
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

    /* =========================
       IMAGE UPLOAD
    ========================== */
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

    /* =========================
       INSERT BLOG
    ========================== */
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

<h1>🚀 Create Blog (AI + Offline CMS)</h1>

<form method="POST" enctype="multipart/form-data" class="card">

    <!-- TITLE -->
    <label>Blog Title</label>
    <input type="text" name="title" required>

    <br><br>

    <!-- AI BUTTON -->
    <button type="submit" name="generate_ai" class="btn">
        ✨ Generate AI Blog (ChatGPT + Offline Fallback)
    </button>

    <br><br>

    <!-- CONTENT -->
    <label>Blog Content</label>
    <textarea name="content" id="editor" rows="10"></textarea>

    <br><br>

    <!-- IMAGE -->
    <label>Featured Image</label>
    <input type="file"
           name="image"
           accept=".jpg,.jpeg,.png,.webp"
           onchange="previewImage(event)">

    <br>

    <img id="preview"
         style="width:220px;margin-top:15px;border-radius:12px;display:none;">

    <br><br>

    <!-- META -->
    <label>Meta Title</label>
    <input type="text" name="meta_title">

    <br><br>

    <label>Meta Description</label>
    <textarea name="meta_description" rows="4"></textarea>

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

<script>
function previewImage(event){
    let reader = new FileReader();
    reader.onload = function(){
        let img = document.getElementById('preview');
        img.src = reader.result;
        img.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>
<script src="pradip/admin/assets/js/admin.js"></script>
<?php include '../layout/footer.php'; ?>