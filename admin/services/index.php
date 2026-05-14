<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* DELETE */

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

/* FETCH */

$result = $conn->query("
    SELECT *
    FROM services
    ORDER BY id DESC
");

include '../layout/header.php';
?>

<section class="admin-page">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

        <h1>Services</h1>

        <a href="create.php" class="btn">
            Add Service
        </a>

    </div>

    <div class="grid">

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="card">

                <h3>
                    <?= htmlspecialchars($row['title']) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($row['description']) ?>
                </p>

                <div style="margin-top:15px;display:flex;gap:10px;">

                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn">
                        Edit
                    </a>

                    <a href="?delete=<?= $row['id'] ?>"
                       class="btn-danger"
                       onclick="return confirm('Delete this service?')">
                        Delete
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<?php include '../layout/footer.php'; ?>