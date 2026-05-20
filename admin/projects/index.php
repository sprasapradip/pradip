<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

/* ================= DELETE ================= */
if(isset($_POST['delete_id'])){

    $id = (int)$_POST['delete_id'];

    // get image
    $stmt = $conn->prepare("SELECT image FROM projects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $img = $stmt->get_result()->fetch_assoc();

    if(!empty($img['image'])){
        $path = __DIR__ . '/../../uploads/' . $img['image'];
        if(file_exists($path)) unlink($path);
    }

    $del = $conn->prepare("DELETE FROM projects WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* ================= PAGINATION ================= */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* TOTAL */
$total = $conn->query("SELECT COUNT(*) as t FROM projects")->fetch_assoc()['t'];
$pages = ceil($total / $limit);

/* FETCH */
$stmt = $conn->prepare("
    SELECT *
    FROM projects
    ORDER BY id DESC
    LIMIT ?, ?
");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$res = $stmt->get_result();

include __DIR__ . '/../layout/header.php';
?>

<style>
.table{
    width:100%;
    border-collapse:collapse;
}
.table th, .table td{
    padding:12px;
    border-bottom:1px solid #eee;
    font-size:14px;
}
.actions{
    position:relative;
}
.menu{
    display:none;
    position:absolute;
    right:0;
    background:#fff;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    border-radius:10px;
    min-width:140px;
    z-index:10;
}
.actions:hover .menu{
    display:block;
}
.menu a, .menu button{
    display:block;
    padding:10px;
    width:100%;
    border:none;
    background:none;
    text-align:left;
    cursor:pointer;
}
</style>

<section class="admin-page">

<h1>Projects</h1>


<div class="table-wrapper">

<table class="pro-table">

<thead>
<tr>
    <th>SN</th>
    <th>Title</th>
    <th>Date</th>
    <th style="text-align:center;">Actions</th>
</tr>
</thead>

<tbody>

<?php $sn = $offset + 1; ?>

<?php while($row = $res->fetch_assoc()): ?>

<tr>

<td><?= $sn++ ?></td>

<td>
<div class="title-text">
    <?= htmlspecialchars($row['title']) ?>
</div>
</td>

<td>
<?= !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-' ?>
</td>

<td style="text-align:center;">

<div class="actions">

    <button class="action-btn">Manage</button>

    <div class="action-menu">

        <a class="preview" href="/project-single.php?slug=<?= urlencode($row['slug'] ?? '') ?>" target="_blank">
            Preview
        </a>

        <a class="edit" href="edit.php?id=<?= $row['id'] ?>">
            Edit
        </a>

        <form method="POST" onsubmit="return confirm('Delete project?')">
            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
            <button class="delete" type="submit">Delete</button>
        </form>

    </div>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>