<?php
define('APP_INIT', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config/guard.php';

include __DIR__ . '/../../layout/header.php';

/* DELETE BLOG */
if(isset($_POST['delete_id'])){

    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM blog WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* FETCH BLOGS */
$res = $conn->query("SELECT * FROM blog ORDER BY id DESC");
?>

<section class="admin-page">

<h1>Blogs</h1>

<?php if(isset($_GET['deleted'])): ?>
    <p style="color:green;">Blog deleted successfully.</p>
<?php endif; ?>

<a href="add.php" class="btn">+ Add Blog</a>

<div class="grid">

<?php if($res->num_rows > 0): ?>

    <?php while($row = $res->fetch_assoc()): ?>

        <div class="card">

            <h3><?= htmlspecialchars($row['title']) ?></h3>

            <p>
                <?= htmlspecialchars(substr($row['content'],0,120)) ?>...
            </p>

            <div style="margin-top:10px;">

                <!-- EDIT -->
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn">
                    Edit
                </a>

                <!-- DELETE -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                    <button type="submit"
                            class="btn-danger"
                            onclick="return confirm('Delete this blog?')">
                        Delete
                    </button>
                </form>

            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p>No blogs found.</p>

<?php endif; ?>

</div>

</section>

<?php include __DIR__ . '/../../layout/footer.php'; ?>