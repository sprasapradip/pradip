<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

$id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT *
    FROM services
    WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$service = $stmt->get_result()->fetch_assoc();

if(!$service){
    die("Service not found");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("
        UPDATE services
        SET title=?, description=?
        WHERE id=?
    ");

    $stmt->bind_param("ssi", $title, $description, $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

    <h1>Edit Service</h1>

    <form method="POST">

        <input type="text"
               name="title"
               value="<?= htmlspecialchars($service['title']) ?>"
               required>

        <textarea name="description"
                  id="editor"
                  rows="6"
                  required><?= htmlspecialchars($service['description']) ?></textarea>

        <button class="btn">
            Update Service
        </button>

    </form>

</section>

<?php include '../layout/footer.php'; ?>