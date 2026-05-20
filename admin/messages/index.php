<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config/guard.php';

/* ================= DELETE ================= */
if(isset($_POST['delete_id'])){

    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM messages WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* ================= MARK READ ================= */
if(isset($_GET['read'])){

    $id = (int) $_GET['read'];

    $stmt = $conn->prepare("UPDATE messages SET status='read' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?success=1");
    exit;
}

/* ================= FETCH ================= */
$result = $conn->query("
    SELECT *
    FROM messages
    ORDER BY id DESC
");

include '../layout/header.php';
?>

<section class="admin-page">

<!-- HEADER -->
<div class="dashboard-header">

    <div>
        <h1>Messages</h1>
        <p class="muted">Manage incoming contact messages</p>
    </div>

</div>

<!-- ALERT -->
<?php if(isset($_GET['deleted'])): ?>
    <div class="alert danger">Message deleted</div>
<?php endif; ?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert success">Marked as read</div>
<?php endif; ?>

<!-- TABLE WRAPPER -->
<div class="table-wrapper">

<table class="pro-table">

<thead>
<tr>
    <th>SN</th>
    <th>Name</th>
    <th>Email</th>
    <th>Status</th>
    <th>Date</th>
    <th style="text-align:center;">Actions</th>
</tr>
</thead>

<tbody>

<?php $sn = 1; ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?= $sn++ ?></td>

<td>
    <div class="title-text">
        <?= htmlspecialchars($row['name']) ?>
    </div>
</td>

<td>
    <?= htmlspecialchars($row['email']) ?>
</td>

<td>

<?php if(($row['status'] ?? '') === 'read'): ?>
    <span class="badge badge-read">Read</span>
<?php else: ?>
    <span class="badge badge-unread">Unread</span>
<?php endif; ?>

</td>

<td>
    <?= !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-' ?>
</td>

<td style="text-align:center;">

<div class="actions">

    <button class="action-btn">Manage</button>

    <div class="action-menu">

        <!-- MARK READ -->
        <?php if(($row['status'] ?? '') !== 'read'): ?>
            <a class="preview" href="?read=<?= $row['id'] ?>">
                ✔ Mark Read
            </a>
        <?php endif; ?>

        <!-- REPLY -->
        <a class="edit"
           href="/pradip/mail/index.php?email=<?= urlencode($row['email']) ?>">
            ↩ Reply
        </a>

        <!-- DELETE -->
        <form method="POST" onsubmit="return confirm('Delete message?')">
            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
            <button class="delete" type="submit">
                🗑 Delete
            </button>
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

<?php include '../layout/footer.php'; ?>