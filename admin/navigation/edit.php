<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT *
    FROM navigation_menu
    WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$menu = $stmt->get_result()->fetch_assoc();

if(!$menu){
    die("Menu not found");
}

/* UPDATE */
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $sort = (int) $_POST['sort_order'];
    $status = $_POST['status'];

    $update = $conn->prepare("
        UPDATE navigation_menu
        SET title=?,
            url=?,
            sort_order=?,
            status=?
        WHERE id=?
    ");

    $update->bind_param(
        "ssisi",
        $title,
        $url,
        $sort,
        $status,
        $id
    );

    $update->execute();

    header("Location:index.php?updated=1");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

<div class="form-card">

    <div class="page-header">
        <div>
            <h1>Edit Navigation</h1>
            <p class="page-subtitle">
                Update navbar item
            </p>
        </div>
    </div>

    <form method="POST" class="pro-form">

        <div class="form-group">

            <label>Menu Title</label>

            <input type="text"
                   name="title"
                   required
                   value="<?= htmlspecialchars($menu['title']) ?>">

        </div>

        <div class="form-group">

            <label>URL</label>

            <input type="text"
                   name="url"
                   required
                   value="<?= htmlspecialchars($menu['url']) ?>">

        </div>

        <div class="form-group">

            <label>Sort Order</label>

            <input type="number"
                   name="sort_order"
                   value="<?= $menu['sort_order'] ?>">

        </div>

        <div class="form-group">

            <label>Status</label>

            <select name="status">

                <option value="active"
                    <?= $menu['status']=='active' ? 'selected' : '' ?>>
                    Active
                </option>

                <option value="hidden"
                    <?= $menu['status']=='hidden' ? 'selected' : '' ?>>
                    Hidden
                </option>

            </select>

        </div>

        <button class="btn" type="submit">
            Update Menu
        </button>

    </form>

</div>

</section>

<?php include '../layout/footer.php'; ?>