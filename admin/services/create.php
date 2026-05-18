<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("
        INSERT INTO services(title, description)
        VALUES(?, ?)
    ");

    $stmt->bind_param("ss", $title, $description);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

    <h1>Add Service</h1>

    <form method="POST">

        <input type="text"
               name="title"
               placeholder="Service Title"
               required>

        <textarea name="description"
                  id="editor"
                  placeholder="Description"
                  rows="6"
                  required></textarea>

        <button class="btn">
            Save Service
        </button>

    </form>

</section>

<?php include '../layout/footer.php'; ?>