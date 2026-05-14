<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM blogs WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

$result = $conn->query("SELECT * FROM blogs ORDER BY id DESC");

include '../layout/header.php';
?>

<section class="admin-page">

    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h1>Blogs</h1>
        <a href="create.php" class="btn">Add Blog</a>
    </div>

    <div class="grid">

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="card">

                <h3><?= htmlspecialchars($row['title']) ?></h3>

                <p>
                    <?= substr(strip_tags($row['content']), 0, 120) ?>...
                </p>

                <div style="margin-top:10px;display:flex;gap:10px;">

                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn">Edit</a>

                    <a href="?delete=<?= $row['id'] ?>"
                       class="btn-danger"
                       onclick="return confirm('Delete blog?')">
                        Delete
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<?php include '../layout/footer.php'; ?>
