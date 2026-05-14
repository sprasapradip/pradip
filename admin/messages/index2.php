<?php
define('APP_INIT', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once '../config/guard.php';

/* =========================
   DELETE MESSAGE (SECURE)
========================= */
if(isset($_POST['delete_id'])){
    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM messages WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* =========================
   MARK AS READ
========================= */
if(isset($_GET['read'])){
    $id = (int) $_GET['read'];

    $stmt = $conn->prepare("UPDATE messages SET `status` = 'read' WHERE id = ?");

if($stmt){
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

    header("Location: index.php?read=1");
    exit;
}




/* =========================
   SEARCH
========================= */
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

$stmt = $conn->prepare("SELECT * FROM messages WHERE name LIKE ? ORDER BY id DESC");
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<section class="admin-page">

<h1>Messages</h1>

<?php if(isset($_GET['deleted'])): ?>
    <p style="color:red;">Message deleted successfully.</p>
<?php endif; ?>

<?php if(isset($_GET['read'])): ?>
    <p style="color:green;">Message marked as read.</p>
<?php endif; ?>

<!-- ================= SEARCH ================= -->

<form method="GET" style="margin-bottom:20px;">
    <input type="text"
           name="search"
           placeholder="Search name..."
           value="<?= htmlspecialchars($search) ?>"
           style="padding:8px; border-radius:6px; border:1px solid #ccc;">

    <button class="btn">Search</button>
</form>

<!-- ================= MESSAGE CARDS ================= -->

<div class="grid">

<?php while($row = $result->fetch_assoc()): ?>

    <div class="card <?= ($row['status'] ?? '') == 'read' ? 'read-card' : '' ?>">

        <h3><?= htmlspecialchars($row['name']) ?></h3>

        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>

        <p>
            <strong>Status:</strong>
            <?php if(($row['status'] ?? '') == 'read'): ?>
                <span class="badge badge-read">Read</span>
            <?php else: ?>
                <span class="badge badge-unread">Unread</span>
            <?php endif; ?>
        </p>

        <div style="margin-top:10px;">

            <!-- MARK READ -->
            <?php if(($row['status'] ?? '') != 'read'): ?>
                <a href="?read=<?= $row['id'] ?>" class="btn">
                    Mark Read
                </a>
            <?php endif; ?>

            <!-- DELETE -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                <button type="button"
                        class="btn-danger delete-btn">
                    Delete
                </button>
            </form>

        </div>

    </div>

<?php endwhile; ?>

</div>

</section>

<!-- ================= DELETE SCRIPT ================= -->

<script>
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function () {

        if(confirm("Are you sure you want to delete this message?")){

            let card = this.closest('.card');
            card.classList.add('deleting');

            setTimeout(() => {
                this.closest('form').submit();
            }, 400);
        }

    });
});
</script>

<?php include '../layout/footer.php'; ?>