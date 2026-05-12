<?php
define('APP_INIT', true);
require_once '../../includes/config.php';
require_once '../auth.php';

if(isset($_POST['save'])){
    $title=$_POST['title'];
    $desc=$_POST['description'];

    $stmt=$conn->prepare("INSERT INTO projects(title,description) VALUES(?,?)");
    $stmt->bind_param("ss",$title,$desc);
    $stmt->execute();

    header("Location: index.php");
}
?>

<form method="POST">
<input name="title" placeholder="Title">
<textarea name="description"></textarea>
<button name="save">Save</button>
</form>