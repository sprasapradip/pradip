<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

function createSlug($string){
    return trim(
        preg_replace('/[^a-z0-9]+/', '-', strtolower($string)),
        '-'
    );
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM blogs WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$blog = $stmt->get_result()->fetch_assoc();

if(!$blog){
    die("Blog not found");
}

/* =========================
   FORCE SAFE STRING VALUES
========================= */
array_walk($blog, function(&$v){
    $v = (string)$v;
});

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');

    $status = $_POST['status'] ?? 'published';
    $featured = isset($_POST['featured']) ? 1 : 0;

    $slug = createSlug($title);

    /* reading time */
    $wordCount = str_word_count(strip_tags($content));
    $reading_time = ceil($wordCount / 200) . ' min read';

    $imageName = $blog['image'];

    /* IMAGE UPLOAD */
    if(!empty($_FILES['image']['name'])){

        $allowed = ['jpg','jpeg','png','webp'];

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){

            $imageName = time().'_'.uniqid().'.'.$ext;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "../../uploads/".$imageName
            );

            /* delete old image */
            if(!empty($blog['image']) && file_exists("../../uploads/".$blog['image'])){
                unlink("../../uploads/".$blog['image']);
            }
        }
    }

    /* UPDATE BLOG */
    $stmt = $conn->prepare("
        UPDATE blogs
        SET
            title=?,
            slug=?,
            content=?,
            image=?,
            meta_title=?,
            meta_description=?,
            keywords=?,
            status=?,
            featured=?,
            reading_time=?,
            updated_at=NOW()
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssssssssssi",
        $title,
        $slug,
        $content,
        $imageName,
        $meta_title,
        $meta_description,
        $keywords,
        $status,
        $featured,
        $reading_time,
        $id
    );

    $stmt->execute();

    header("Location: index.php?updated=1");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

<h1>Edit Blog</h1>

<form method="POST" enctype="multipart/form-data" class="card">

    <!-- TITLE -->
    <label>Title</label>
    <input type="text"
           name="title"
           value="<?= htmlspecialchars((string)$blog['title']) ?>"
           required>

    <br><br>

    <!-- CONTENT -->
    <label>Content</label>
    <textarea name="content"
              id="editor"
              rows="10"
              required><?= htmlspecialchars((string)$blog['content']) ?></textarea>

    <br><br>

    <!-- CURRENT IMAGE -->
    <?php if(!empty($blog['image'])): ?>
        <img src="../../uploads/<?= htmlspecialchars((string)$blog['image']) ?>"
             style="width:220px;height:140px;object-fit:cover;border-radius:12px;margin-bottom:15px;">
    <?php endif; ?>

    <!-- IMAGE -->
    <input type="file"
           name="image"
           accept=".jpg,.jpeg,.png,.webp"
           onchange="previewImage(event)">

    <br>

    <img id="preview"
         style="width:220px;margin-top:15px;border-radius:12px;display:none;">

    <br><br>

    <!-- META TITLE -->
    <label>Meta Title</label>
    <input type="text"
           name="meta_title"
           value="<?= htmlspecialchars((string)$blog['meta_title']) ?>">

    <br><br>

    <!-- META DESCRIPTION -->
    <label>Meta Description</label>
    <textarea name="meta_description"
              rows="4"><?= htmlspecialchars((string)$blog['meta_description']) ?></textarea>

    <br><br>

    <!-- KEYWORDS -->
    <label>Keywords</label>
    <input type="text"
           name="keywords"
           value="<?= htmlspecialchars((string)$blog['keywords']) ?>">

    <br><br>

    <!-- STATUS -->
    <label>Status</label>
    <select name="status">

        <option value="published" <?= $blog['status'] === 'published' ? 'selected' : '' ?>>
            Published
        </option>

        <option value="draft" <?= $blog['status'] === 'draft' ? 'selected' : '' ?>>
            Draft
        </option>

    </select>

    <br><br>

    <!-- FEATURED -->
    <label style="display:flex;align-items:center;gap:10px;">

        <input type="checkbox"
               name="featured"
               <?= !empty($blog['featured']) ? 'checked' : '' ?>>

        Featured Blog

    </label>

    <br><br>

    <button class="btn">
        Update Blog
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

<?php include '../layout/footer.php'; ?>