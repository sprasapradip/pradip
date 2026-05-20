<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* =========================
   DELETE BLOG (SAFE)
========================= */
if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    // Get image first
    $stmt = $conn->prepare("SELECT image FROM blogs WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $img = $stmt->get_result()->fetch_assoc();

    // Delete image file
    if(!empty($img['image'])){
        $path = "../../uploads/" . $img['image'];
        if(file_exists($path)){
            unlink($path);
        }
    }

    // Delete blog
    $del = $conn->prepare("DELETE FROM blogs WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* =========================
   PAGINATION
========================= */
$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* TOTAL */
$total = $conn->query("SELECT COUNT(*) as t FROM blogs")->fetch_assoc()['t'];
$totalPages = ceil($total / $limit);

/* FETCH */
$stmt = $conn->prepare("
    SELECT *
    FROM blogs
    ORDER BY id DESC
    LIMIT ?, ?
");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();

$result = $stmt->get_result();

include '../layout/header.php';
?>

<section class="admin-page">

<!-- HEADER -->
<div class="dashboard-header">

    <div>
        <h1>Blogs</h1>
        <p class="muted">Total Blogs: <?= number_format($total) ?></p>
    </div>

    <div style="display:flex;gap:10px;">
        <a href="create.php" class="btn">+ Add Blog</a>
        <a href="import.php" class="btn">Import</a>
    </div>

</div>

<!-- SUCCESS -->
<?php if(isset($_GET['deleted'])): ?>
    <div style="
        background:#dcfce7;
        color:#166534;
        padding:12px 15px;
        border-radius:10px;
        margin-bottom:15px;
    ">
        Blog deleted successfully.
    </div>
<?php endif; ?>

<!-- TABLE -->
<div class="table-wrapper">

<table class="pro-table">

<thead>
<tr>
    <th>SN</th>
    <th>Title</th>
    <th>Views</th>
    <th>Date</th>
    <th>Status</th>
    <th style="text-align:center;">Actions</th>
</tr>
</thead>

<tbody>

<?php $sn = $offset + 1; ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?= $sn++ ?></td>

<td>
<div class="title-text">
    <?= htmlspecialchars($row['title']) ?>
</div>
</td>

<td>👁 <?= (int)($row['views'] ?? 0) ?></td>

<td>
<?= !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-' ?>
</td>

<td>

<form method="POST" action="update-status.php">

    <input type="hidden" name="id" value="<?= $row['id'] ?>">

    <select name="status" class="status-select" onchange="this.form.submit()">

        <option value="published" <?= $row['status']=='published'?'selected':'' ?>>
            Published
        </option>

        <option value="draft" <?= $row['status']=='draft'?'selected':'' ?>>
            Draft
        </option>

    </select>

</form>

</td>

<td style="text-align:center;">

<div class="actions">

    <button class="action-btn">Manage</button>

    <div class="action-menu">

        <a class="preview"
           href="../../blog/<?= urlencode($row['slug'] ?? '') ?>"
           target="_blank">
            Preview
        </a>

        <a class="edit" href="edit.php?id=<?= $row['id'] ?>">
            Edit
        </a>

        <a class="delete"
           href="?delete=<?= $row['id'] ?>"
           onclick="return confirm('Delete this blog permanently?')">
            Delete
        </a>

    </div>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

<!-- PAGINATION -->
<?php if($totalPages > 1): ?>

<div class="pagination">

    <?php if($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>">← Prev</a>
    <?php endif; ?>

    <?php for($i = 1; $i <= $totalPages; $i++): ?>
        <a class="<?= $i==$page?'active':'' ?>" href="?page=<?= $i ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>">Next →</a>
    <?php endif; ?>

</div>

<?php endif; ?>

</section>

<?php include '../layout/footer.php'; ?>