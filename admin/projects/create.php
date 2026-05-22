<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once '../config/guard.php';

/* =========================
   SLUG FUNCTION
========================= */
function createSlug($text){
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/* =========================
   SAVE PROJECT
========================= */
if(isset($_POST['save'])){

    $title    = trim($_POST['title']);
    $desc     = trim($_POST['description']);
    $author   = trim($_POST['author']);
    $category = trim($_POST['category']);
    $tags     = trim($_POST['tags']);

    if($title == '' || $desc == ''){
        die("Title and Description required");
    }

    /* SLUG */
    $slug = createSlug($title);

    $check = $conn->prepare("SELECT id FROM projects WHERE slug=?");
    $check->bind_param("s", $slug);
    $check->execute();
    $res = $check->get_result();

    if($res && $res->num_rows > 0){
        $slug .= '-' . time();
    }

    /* IMAGE UPLOAD */
    $imageName = null;

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){

            $imageName = time().'_'.rand(1000,9999).'.'.$ext;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                __DIR__ . '/../../uploads/' . $imageName
            );
        }
    }

    /* INSERT */
    $stmt = $conn->prepare("
        INSERT INTO projects
        (title, slug, description, image, author, category, tags, views)
        VALUES (?,?,?,?,?,?,?,0)
    ");

    if(!$stmt){
        die("DB Error: ".$conn->error);
    }

    $stmt->bind_param(
        "sssssss",
        $title,
        $slug,
        $desc,
        $imageName,
        $author,
        $category,
        $tags
    );

    if(!$stmt->execute()){
        die("Insert Error: ".$stmt->error);
    }

    header("Location: index.php?added=1");
    exit;
}

include __DIR__ . '/../layout/header.php';
?>

<style>
.form-container{
    max-width: 900px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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

<h2>Create New Project</h2>

<form method="POST" enctype="multipart/form-data">

    <!-- TITLE -->
    <label>Title</label>
    <input type="text" name="title" required>

    <!-- AUTHOR -->
    <label>Author</label>
    <input type="text" name="author" placeholder="Admin" required>

    <!-- CATEGORY -->
    <label>Category</label>
    <input type="text" name="category" placeholder="Engineering, Electrical..." required>

    <!-- TAGS -->
    <label>Tags (comma separated)</label>
    <input type="text" name="tags" placeholder="cable car, safety, electrical">

    <!-- DESCRIPTION -->
    <label>Description</label>

    <input type="hidden" name="description" id="description">
    <textarea id="editor"></textarea>

    <!-- IMAGE -->
    <label>Image</label>
    <input type="file" name="image">

    <br><br>

    <button type="submit" name="save">Save Project</button>

</form>

</div>

<!-- CKEDITOR -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>

let editorInstance;

ClassicEditor
    .create(document.querySelector('#editor'))
    .then(editor => {
        editorInstance = editor;
    })
    .catch(error => console.error(error));

/* FIX: IMPORTANT */
document.querySelector("form").addEventListener("submit", function () {
    document.getElementById("description").value = editorInstance.getData();
});

</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>