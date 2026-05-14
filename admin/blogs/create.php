<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

function createSlug($string){
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $slug = createSlug($title);

    $imageName = '';

    if(!empty($_FILES['image']['name'])){
        $imageName = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/".$imageName);
    }

    $stmt = $conn->prepare("
        INSERT INTO blogs(title, slug, content, image)
        VALUES(?,?,?,?)
    ");

    $stmt->bind_param("ssss", $title, $slug, $content, $imageName);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

<h1>Add Blog</h1>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="Title" required>

    <textarea name="content" rows="8" placeholder="Content" required></textarea>

    <!-- IMAGE PREVIEW -->
    <input type="file" name="image" onchange="previewImage(event)">
    <br>
    <img id="preview" style="width:200px;margin-top:10px;border-radius:10px;display:none;">

    <button class="btn">Publish</button>

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