<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once '../config/guard.php';

/* =========================
   ADD PROJECT
========================= */
if(isset($_POST['save'])){

    $title = $_POST['title'];
    $desc  = $_POST['description'];

    $imageName = null;

    // IMAGE UPLOAD
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $allowed = ['jpg','jpeg','png','webp'];

        $fileExt = strtolower(
            pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)
        );

        if(in_array($fileExt, $allowed)){

            $imageName = time() . '_' . rand(1000,9999) . '.' . $fileExt;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                __DIR__ . '/../../uploads/' . $imageName
            );
        }
    }

    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO projects(title, description, image)
        VALUES(?,?,?)
    ");

    $stmt->bind_param("sss", $title, $desc, $imageName);

    $stmt->execute();

    header("Location: index.php?added=1");
    exit;
}

include __DIR__ . '/../layout/header.php';
?>

<section class="admin-page">

<h1>Add Project</h1>

<form method="POST" enctype="multipart/form-data">

    <input type="text"
           name="title"
           placeholder="Title"
           required>

    <br><br>

    <textarea name="description"
              id="editor"
              placeholder="Description"
              required></textarea>

    <br><br>

    <input type="file"
           name="image"
           accept="image/*">

    <br><br>

    <button type="submit"
            name="save"
            class="btn">

        Save

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