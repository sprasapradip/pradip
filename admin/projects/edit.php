<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM projects WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    die("Project not found");
}

if (isset($_POST['update'])) {

    $title = $_POST['title'];
    $desc  = $_POST['description'];

    $update = $conn->prepare("
        UPDATE projects 
        SET title=?, description=? 
        WHERE id=?
    ");

    $update->bind_param("ssi", $title, $desc, $id);
    $update->execute();

    header("Location: index.php");
    exit;
}
?>

<form method="POST">

    <input 
        name="title"
        value="<?= htmlspecialchars($project['title']) ?>"
        placeholder="Title"
    >

    <textarea name="description"><?= htmlspecialchars($project['description']) ?></textarea>

    <button name="update">
        Update
    </button>

</form>