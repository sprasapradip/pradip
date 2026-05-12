<?php include '../header.php'; include '../../config.php'; ?>

<?php
if(isset($_POST['save'])){
    $stmt = $conn->prepare("INSERT INTO blog(title,content) VALUES(?,?)");
    $stmt->bind_param("ss", $_POST['title'], $_POST['content']);
    $stmt->execute();
    header("Location: index.php");
}
?>

<h2>Add Blog</h2>

<form method="POST">
    <input name="title">
    <textarea name="content"></textarea>
    <button name="save">Publish</button>
</form>

<?php include '../footer.php'; ?>