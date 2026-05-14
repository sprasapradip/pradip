<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* DELETE */
if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM experience WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

/* FETCH */
$result = $conn->query("
    SELECT *
    FROM experience
    ORDER BY id DESC
");

include '../layout/header.php';
?>

<section class="admin-page">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h1>Experience</h1>
        <a href="create.php" class="btn">Add Experience</a>
    </div>

    <div class="grid">

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="card">

                <h3><?= htmlspecialchars($row['title']) ?></h3>

                <p><strong><?= htmlspecialchars($row['company']) ?></strong></p>

                <p><?= htmlspecialchars($row['description']) ?></p>

                <div style="margin-top:10px;">
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn-danger"
                       onclick="return confirm('Delete this experience?')">
                        Delete
                    </a>
                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<?php include '../layout/footer.php'; ?>