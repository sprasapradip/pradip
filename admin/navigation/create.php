<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $sort = (int) $_POST['sort_order'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        INSERT INTO navigation_menu
        (title, url, sort_order, status)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("ssis",
        $title,
        $url,
        $sort,
        $status
    );

    $stmt->execute();

    header("Location:index.php?created=1");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

<div class="form-card">

    <div class="page-header">
        <div>
            <h1>Add Navigation Menu</h1>
            <p class="page-subtitle">
                Create new navbar item
            </p>
        </div>
    </div>

    <form method="POST" class="pro-form">

        <div class="form-group">
            <label>Menu Title</label>

            <input type="text"
                   name="title"
                   required
                   placeholder="Blogs">
        </div>

        <div class="form-group">
            <label>URL</label>

            <input type="text"
                   name="url"
                   required
                   placeholder="/pradip/blogs.php">
        </div>

        <div class="form-group">
            <label>Sort Order</label>

            <input type="number"
                   name="sort_order"
                   value="1">
        </div>

        <div class="form-group">
            <label>Status</label>

            <select name="status">

                <option value="active">
                    Active
                </option>

                <option value="hidden">
                    Hidden
                </option>

            </select>
        </div>

        <button class="btn" type="submit">
            Save Menu
        </button>

    </form>

</div>

</section>

<?php include '../layout/footer.php'; ?>