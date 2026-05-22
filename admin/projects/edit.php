<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once '../config/guard.php';

/* =========================
   GET PROJECT
========================= */
if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM projects WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if(!$project){
    header("Location: index.php");
    exit;
}

/* =========================
   UPDATE PROJECT
========================= */
if(isset($_POST['update'])){

    $title    = trim($_POST['title']);
    $desc     = trim($_POST['description']);
    $author   = trim($_POST['author']);
    $category = trim($_POST['category']);
    $tags     = trim($_POST['tags']);

    if($title == '' || $desc == ''){
        die("Title and description required");
    }

    $imageName = $project['image'];

    /* ================= IMAGE UPLOAD ================= */
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $allowed = ['jpg','jpeg','png','webp'];
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if(in_array($fileExt, $allowed)){

            /* DELETE OLD IMAGE */
            if(!empty($project['image'])){
                $oldPath = __DIR__ . '/../../uploads/' . $project['image'];
                if(file_exists($oldPath)){
                    unlink($oldPath);
                }
            }

            $imageName = time().'_'.rand(1000,9999).'.'.$fileExt;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                __DIR__ . '/../../uploads/' . $imageName
            );
        }
    }

    /* ================= UPDATE QUERY ================= */
    $stmt = $conn->prepare("
        UPDATE projects
        SET title=?, description=?, image=?, author=?, category=?, tags=?
        WHERE id=?
    ");

    if(!$stmt){
        die("DB Error: ".$conn->error);
    }

    $stmt->bind_param(
        "ssssssi",
        $title,
        $desc,
        $imageName,
        $author,
        $category,
        $tags,
        $id
    );

    if(!$stmt->execute()){
        die("Update Error: ".$stmt->error);
    }

    header("Location: index.php?updated=1");
    exit;
}

include __DIR__ . '/../layout/header.php';
?>

<style>
.form-container{
    max-width:900px;
    margin:40px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

input, textarea{
    width:100%;
    padding:10px;
    margin-top:8px;
    margin-bottom:15px;
    border:1px solid #ddd;
    border-radius:8px;
}

label{
    font-weight:bold;
}

button{
    background:#0f172a;
    color:#fff;
    padding:10px 20px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
</style>

<div class="form-container">

<h2>Edit Project</h2>

<form method="POST" enctype="multipart/form-data">

    <!-- TITLE -->
    <label>Title</label>
    <input type="text" name="title"
           value="<?= htmlspecialchars($project['title']) ?>" required>

    <!-- AUTHOR -->
    <label>Author</label>
    <input type="text" name="author"
           value="<?= htmlspecialchars($project['author'] ?? 'Admin') ?>" required>

    <!-- CATEGORY -->
    <label>Category</label>
    <input type="text" name="category"
           value="<?= htmlspecialchars($project['category'] ?? '') ?>" required>

    <!-- TAGS -->
    <label>Tags</label>
    <input type="text" name="tags"
           value="<?= htmlspecialchars($project['tags'] ?? '') ?>">

    <!-- DESCRIPTION -->
    <label>Description</label>

    <!-- FIXED CKEDITOR 5 -->
    <input type="hidden" name="description" id="description">
    <textarea id="editor"><?= htmlspecialchars($project['description']) ?></textarea>

    <!-- CURRENT IMAGE -->
    <?php if(!empty($project['image'])): ?>
        <label>Current Image</label>
        <br>
        <img src="../../uploads/<?= htmlspecialchars($project['image']) ?>"
             style="width:200px;border-radius:10px;margin-bottom:10px;">
    <?php endif; ?>

    <!-- NEW IMAGE -->
    <label>Change Image</label>
    <input type="file" name="image">

    <br><br>

    <button type="submit" name="update">Update Project</button>

</form>

</div>

<!-- CKEDITOR 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>

let editorInstance;

ClassicEditor
    .create(document.querySelector('#editor'))
    .then(editor => {
        editorInstance = editor;
    })
    .catch(error => console.error(error));

/* IMPORTANT FIX */
document.querySelector("form").addEventListener("submit", function () {
    document.getElementById("description").value = editorInstance.getData();
});

</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>