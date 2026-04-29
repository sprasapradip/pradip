<?php include '../auth.php'; include '../../config.php'; ?>

<?php
$conn->query("DELETE FROM projects WHERE id=".$_GET['id']);
header("Location: index.php");