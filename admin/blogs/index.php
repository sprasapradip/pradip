<?php include '../header.php'; include '../../config.php'; ?>

<h2>Blog</h2>
<a href="add.php" class="btn">Add Post</a>

<?php
$res = $conn->query("SELECT * FROM blog ORDER BY id DESC");
while($row = $res->fetch_assoc()):
?>

<div class="card">
    <h3><?= $row['title'] ?></h3>
    <p><?= substr($row['content'],0,100) ?></p>
</div>

<?php endwhile; ?>

<?php include '../footer.php'; ?>