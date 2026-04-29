<?php include '../header.php'; include '../../config.php'; ?>

<h2>Projects</h2>
<a href="add.php" class="btn">Add Project</a>

<?php
$res = $conn->query("SELECT * FROM projects ORDER BY id DESC");
while($row = $res->fetch_assoc()):
?>

<div class="card">
    <h3><?= $row['title'] ?></h3>
    <p><?= $row['description'] ?></p>
    <a href="delete.php?id=<?= $row['id'] ?>">Delete</a>
</div>

<?php endwhile; ?>

<?php include '../footer.php'; ?>