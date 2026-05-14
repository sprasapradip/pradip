<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM experience WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

if(!$data){
    die("Not found");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $company = trim($_POST['company']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("
        UPDATE experience
        SET title=?, company=?, description=?
        WHERE id=?
    ");

    $stmt->bind_param("sssi", $title, $company, $description, $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

    <h1>Edit Experience</h1>

    <form method="POST">

        <input type="text"
               name="title"
               value="<?= htmlspecialchars($data['title']) ?>"
               required>

        <input type="text"
               name="company"
               value="<?= htmlspecialchars($data['company']) ?>"
               required>

        <textarea name="description" rows="6" required><?= htmlspecialchars($data['description']) ?></textarea>

        <button class="btn">Update</button>

    </form>

</section>

<?php include '../layout/footer.php'; ?>