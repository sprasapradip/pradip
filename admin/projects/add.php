<<<<<<< HEAD
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

=======
<?php include '../header.php'; include '../../config.php'; ?>

<?php
if(isset($_POST['save'])){
    $stmt = $conn->prepare("INSERT INTO projects(title,description) VALUES(?,?)");
    $stmt->bind_param("ss", $_POST['title'], $_POST['description']);
    $stmt->execute();
>>>>>>> fe19f5faa741cfcbb315602c1db3bd7e772eac19
    header("Location: index.php");
}
?>

<<<<<<< HEAD
<form method="POST">
<input name="title" placeholder="Title">
<textarea name="description"></textarea>
<button name="save">Save</button>
</form>
=======
<h2>Add Project</h2>

<form method="POST">
    <input name="title" placeholder="Title">
    <textarea name="description"></textarea>
    <button name="save">Save</button>
</form>

<?php include '../footer.php'; ?>
>>>>>>> fe19f5faa741cfcbb315602c1db3bd7e772eac19
