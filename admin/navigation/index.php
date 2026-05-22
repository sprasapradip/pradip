<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM navigation_menu WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location:index.php");
    exit;
}

$result = $conn->query("
    SELECT *
    FROM navigation_menu
    ORDER BY sort_order ASC
");

include '../layout/header.php';
?>

<section class="admin-page">

<div class="page-header">

    <div>
        <h1>Navigation Menu</h1>
        <p class="page-subtitle">
            Manage website navbar items
        </p>
    </div>

    <a href="create.php" class="btn">
        + Add Menu
    </a>

</div>

<div class="table-wrapper">

<table class="pro-table">

<thead>
<tr>
    <th>SN</th>
    <th>Title</th>
    <th>URL</th>
    <th>Status</th>
    <th>Sort</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php $sn = 1; ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?= $sn++ ?></td>

<td><?= htmlspecialchars($row['title']) ?></td>

<td><?= htmlspecialchars($row['url']) ?></td>

<td><?= htmlspecialchars($row['status']) ?></td>

<td><?= $row['sort_order'] ?></td>

<td>

<div class="actions">

<button class="action-btn">
Manage
</button>

<div class="action-menu">

<a class="edit"
   href="edit.php?id=<?= $row['id'] ?>">
   ✏ Edit
</a>

<a class="delete"
   href="?delete=<?= $row['id'] ?>"
   onclick="return confirm('Delete menu?')">
   🗑 Delete
</a>

</div>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</section>

<?php include '../layout/footer.php'; ?>