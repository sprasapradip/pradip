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

$result = $stmt->get_result();

$project = $result->fetch_assoc();

if(!$project){

    header("Location: index.php");
    exit;
}

/* =========================
   UPDATE PROJECT
========================= */
if(isset($_POST['update'])){

    $title = $_POST['title'];
    $desc  = $_POST['description'];

    $imageName = $project['image'];

    // IMAGE UPLOAD
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $allowed = ['jpg','jpeg','png','webp'];

        $fileExt = strtolower(
            pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)
        );

        if(in_array($fileExt, $allowed)){

            // DELETE OLD IMAGE
            if(!empty($project['image'])){

                $oldPath = __DIR__ . '/../../uploads/' . $project['image'];

                if(file_exists($oldPath)){
                    unlink($oldPath);
                }
            }

            // UPLOAD NEW IMAGE
            $imageName = time() . '_' . rand(1000,9999) . '.' . $fileExt;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                __DIR__ . '/../../uploads/' . $imageName
            );
        }
    }

    // UPDATE
    $stmt = $conn->prepare("
        UPDATE projects
        SET title=?, description=?, image=?
        WHERE id=?
    ");

    $stmt->bind_param("sssi", $title, $desc, $imageName, $id);

    $stmt->execute();

    header("Location: index.php?updated=1");
    exit;
}

include __DIR__ . '/../layout/header.php';
?>

<section class="admin-page">

<h1>Edit Project</h1>

<form method="POST" enctype="multipart/form-data">

    <input type="text"
           name="title"
           value="<?= htmlspecialchars($project['title']) ?>"
           required>

    <br><br>

    <textarea id="editor"
     name="description"
              required><?= htmlspecialchars($project['description']) ?></textarea>

    <br><br>

    <?php if(!empty($project['image'])): ?>

        <img src="../../uploads/<?= htmlspecialchars($project['image']) ?>"
             style="width:200px; border-radius:8px; margin-bottom:10px;">

        <br>

    <?php endif; ?>

    <input type="file"
           name="image"
           accept="image/*">

    <br><br>

    <button type="submit"
            name="update"
            class="btn">

        Update

    </button>

    <a href="index.php" class="btn">
        Back
    </a>

</form>

</section>

<!-- CKEDITOR -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<script>
    CKEDITOR.replace('editor', {
        height: 300,
        removeButtons: '',
    });
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>