<?php
define('APP_INIT', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once '../config/guard.php';

/* DELETE PROJECT (Secure) */
if(isset($_POST['delete_id'])){

    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* FETCH PROJECTS */
$res = $conn->query("SELECT * FROM projects ORDER BY id DESC");

include __DIR__ . '/../layout/header.php';
?>

<section class="admin-page">

<h1>Projects</h1>

<?php if(isset($_GET['deleted'])): ?>
    <p style="color:green;">Project deleted successfully.</p>
<?php endif; ?>

<a href="add.php" class="btn">+ Add Project</a>

<div class="grid">

<?php if($res->num_rows > 0): ?>

    <?php while($row = $res->fetch_assoc()): ?>

        <div class="card">

            <h3><?= htmlspecialchars($row['title']) ?></h3>

            <p><?= htmlspecialchars($row['description']) ?></p>

            <div style="margin-top:10px;">

                <!-- EDIT BUTTON -->
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn">
                    Edit
                </a>

                <!-- DELETE FORM -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                    <button type="submit"
                            class="btn-danger"
                            onclick="return confirm('Are you sure you want to delete this project?')">
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
