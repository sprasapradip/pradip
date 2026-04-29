<?php include '../header.php'; include '../../config.php'; ?>

<?php
if(isset($_POST['save'])){
    $stmt = $conn->prepare("INSERT INTO projects(title,description) VALUES(?,?)");
    $stmt->bind_param("ss", $_POST['title'], $_POST['description']);
    $stmt->execute();
    header("Location: index.php");
}
?>

<h2>Add Project</h2>

<form method="POST">
    <input name="title" placeholder="Title">
    <textarea name="description"></textarea>
    <button name="save">Save</button>
</form>

<?php include '../footer.php'; ?>