<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

function createSlug($string){
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($string)), '-');
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM blogs WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if(!$blog){
    die("Blog not found");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $slug = createSlug($title);

    $imageName = $blog['image'];

    if(!empty($_FILES['image']['name'])){

        $imageName = time().'_'.$_FILES['image']['name'];

        move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/".$imageName);

        if(!empty($blog['image']) && file_exists("../../uploads/".$blog['image'])){
            unlink("../../uploads/".$blog['image']);
        }
    }

    $stmt = $conn->prepare("
        UPDATE blogs
        SET title=?, slug=?, content=?, image=?
        WHERE id=?
    ");

    $stmt->bind_param("ssssi", $title, $slug, $content, $imageName, $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

<h1>Edit Blog</h1>

<form method="POST" enctype="multipart/form-data">

    <input type="text"
           name="title"
           value="<?= htmlspecialchars($blog['title']) ?>"
           required>

    <textarea name="content"  id="editor" rows="8" required><?= htmlspecialchars($blog['content']) ?></textarea>

    <!-- CURRENT IMAGE -->
    <?php if(!empty($blog['image'])): ?>
        <img src="../../uploads/<?= $blog['image'] ?>"
             style="width:200px;height:120px;object-fit:cover;border-radius:10px;margin-bottom:10px;">
    <?php endif; ?>

    <!-- NEW IMAGE -->
    <input type="file" name="image" onchange="previewImage(event)">

    <img id="preview" style="width:200px;margin-top:10px;border-radius:10px;display:none;">

    <button class="btn">Update Blog</button>

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