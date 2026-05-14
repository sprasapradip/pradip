<?php
define('APP_INIT', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once '../config/guard.php';

/* =========================
   DELETE PROJECT
========================= */
if(isset($_POST['delete_id'])){
    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM projects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* =========================
   ADD OR UPDATE PROJECT
========================= */
if(isset($_POST['save'])){

    $title = $_POST['title'];
    $desc  = $_POST['description'];

    // IMAGE UPLOAD
    $imageName = null;

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $allowed = ['jpg','jpeg','png','webp'];
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if(in_array($fileExt, $allowed)){

            $imageName = time() . '_' . rand(1000,9999) . '.' . $fileExt;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                __DIR__ . '/../../uploads/' . $imageName
            );
        }
    }

    // UPDATE
    if(!empty($_POST['id'])){

        $id = (int) $_POST['id'];

        if($imageName){
            $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, image=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $desc, $imageName, $id);
        } else {
            $stmt = $conn->prepare("UPDATE projects SET title=?, description=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $desc, $id);
        }

        $stmt->execute();
        header("Location: index.php?updated=1");
        exit;
    }

    // INSERT
    else{

        $stmt = $conn->prepare("INSERT INTO projects(title, description, image) VALUES(?,?,?)");
        $stmt->bind_param("sss", $title, $desc, $imageName);
        $stmt->execute();

        header("Location: index.php?added=1");
        exit;
    }
}

/* =========================
   EDIT DATA
========================= */
$edit = null;

if(isset($_GET['edit'])){
    $id = (int) $_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM projects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit = $result->fetch_assoc();
}

/* =========================
   FETCH PROJECTS
========================= */
$res = $conn->query("SELECT * FROM projects ORDER BY id DESC");

include __DIR__ . '/../layout/header.php';
?>

<section class="admin-page">

<h1>Projects</h1>

<h2><?= $edit ? "Edit Project" : "Add Project" ?></h2>

<form method="POST" enctype="multipart/form-data">

    <?php if($edit): ?>
        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
    <?php endif; ?>

    <input type="text"
           name="title"
           placeholder="Title"
           value="<?= htmlspecialchars($edit['title'] ?? '') ?>"
           required>

    <br><br>

    <textarea name="description"
              placeholder="Description"
              required><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>

    <br><br>

    <input type="file" name="image" accept="image/*">

    <br><br>

    <button type="submit" name="save" class="btn">
        <?= $edit ? "Update" : "Save" ?>
    </button>

    <?php if($edit): ?>
        <a href="index.php" class="btn">Cancel</a>
    <?php endif; ?>

</form>

<hr>

<div class="grid">

<?php while($row = $res->fetch_assoc()): ?>

    <div class="card">

        <?php if(!empty($row['image'])): ?>
            <img src="../../uploads/<?= htmlspecialchars($row['image']) ?>"
                 style="width:100%; border-radius:8px; margin-bottom:10px;">
        <?php endif; ?>

        <h3><?= htmlspecialchars($row['title']) ?></h3>

        <p><?= htmlspecialchars($row['description']) ?></p>

        <div style="margin-top:10px;">

            <a href="index.php?edit=<?= $row['id'] ?>" class="btn">
                Edit
            </a>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                <button type="submit"
                        class="btn-danger"
                        onclick="return confirm('Are you sure?')">
                    Delete
                </button>
            </form>

        </div>

    </div>

<?php endwhile; ?>

</div>

</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>
