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

    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
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

    // UPDATE
    if(!empty($_POST['id'])){
        $id = (int) $_POST['id'];

        $stmt = $conn->prepare("UPDATE projects SET title=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $desc, $id);
        $stmt->execute();

        header("Location: index.php?updated=1");
        exit;
    }

    // INSERT
    else{
        $stmt = $conn->prepare("INSERT INTO projects(title, description) VALUES(?, ?)");
        $stmt->bind_param("ss", $title, $desc);
        $stmt->execute();

        header("Location: index.php?added=1");
        exit;
    }
}

/* =========================
   EDIT MODE DATA
========================= */
$editData = null;

if(isset($_GET['edit'])){
    $id = (int) $_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM projects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $editData = $result->fetch_assoc();
}

/* =========================
   FETCH ALL PROJECTS
========================= */
$res = $conn->query("SELECT * FROM projects ORDER BY id DESC");

include __DIR__ . '/../layout/header.php';
?>

<section class="admin-page">

<h1>Projects</h1>

<?php if(isset($_GET['added'])): ?>
    <p style="color:green;">Project added successfully.</p>
<?php endif; ?>

<?php if(isset($_GET['updated'])): ?>
    <p style="color:blue;">Project updated successfully.</p>
<?php endif; ?>

<?php if(isset($_GET['deleted'])): ?>
    <p style="color:red;">Project deleted successfully.</p>
<?php endif; ?>

<!-- =========================
     ADD / EDIT FORM
========================= -->

<h2><?= $editData ? 'Edit Project' : 'Add Project' ?></h2>

<form method="POST">

    <?php if($editData): ?>
        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
    <?php endif; ?>

    <input type="text"
           name="title"
           placeholder="Title"
           value="<?= htmlspecialchars($editData['title'] ?? '') ?>"
           required>

    <br><br>

    <textarea name="description"
              placeholder="Description"
              required><?= htmlspecialchars($editData['description'] ?? '') ?></textarea>

    <br><br>

    <button type="submit" name="save" class="btn">
        <?= $editData ? 'Update' : 'Save' ?>
    </button>

    <?php if($editData): ?>
        <a href="project.php" class="btn">Cancel</a>
    <?php endif; ?>

</form>

<hr>

<!-- =========================
     PROJECT LIST
========================= -->

<div class="grid">

<?php if($res->num_rows > 0): ?>

    <?php while($row = $res->fetch_assoc()): ?>

        <div class="card">

            <h3><?= htmlspecialchars($row['title']) ?></h3>

            <p><?= htmlspecialchars($row['description']) ?></p>

            <div style="margin-top:10px;">

                <!-- EDIT -->
                <a href="project.php?edit=<?= $row['id'] ?>" class="btn">
                    Edit
                </a>

                <!-- DELETE -->
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

<?php else: ?>

    <p>No projects found.</p>

<?php endif; ?>

</div>

</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>