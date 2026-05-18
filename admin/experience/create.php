<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $company = trim($_POST['company']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("
        INSERT INTO experience(title, company, description)
        VALUES(?,?,?)
    ");

    $stmt->bind_param("sss", $title, $company, $description);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

    <h1>Add Experience</h1>

    <form method="POST">

        <input type="text" name="title" placeholder="Job Title" required>

        <input type="text" name="company" placeholder="Company Name" required>

        <textarea name="description"  id="editor" rows="6" placeholder="Description" required></textarea>

        <button class="btn">Save</button>

    </form>

</section>

<?php include '../layout/footer.php'; ?>