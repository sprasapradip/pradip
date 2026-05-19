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

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

        <h1>Blogs</h1>

        <div style="display:flex;gap:10px;">

            <a href="create.php" class="btn">
                Add Blog
            </a>

            <a href="import.php" class="btn">
                Import Blog
            </a>

        </div>

    </div>

    <div class="grid">

        <?php while($row = $result->fetch_assoc()): ?>

            <?php
                $slug = strtolower(trim(
                    preg_replace('/[^A-Za-z0-9-]+/', '-', $row['title'])
                ));

                $blog_link = "../../blog/" . $slug;
            ?>

            <div class="card">

                <?php if(!empty($row['image'])): ?>

                    <img src="../../uploads/blogs/<?= $row['image'] ?>"
                         style="width:100%;height:180px;object-fit:cover;border-radius:10px;">

                <?php endif; ?>

                <h3 style="margin-top:15px;">
                    <?= htmlspecialchars($row['title']) ?>
                </h3>

                <p>
                    <?= substr(strip_tags($row['content']), 0, 120) ?>...
                </p>

                <div style="margin-top:15px;display:flex;flex-wrap:wrap;gap:10px;">

                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn">
                        Edit
                    </a>

                    <a href="?delete=<?= $row['id'] ?>"
                       class="btn-danger"
                       onclick="return confirm('Delete blog?')">
                        Delete
                    </a>

                    <a href="<?= $blog_link ?>"
                       target="_blank"
                       class="btn">
                        View
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<?php include '../layout/footer.php'; ?>