<?php
define('APP_INIT', true);
require_once '../../includes/config.php';

// DELETE (POST SAFE)
if(isset($_POST['delete_id'])){
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM messages WHERE id=$id");
}

// MARK READ
if(isset($_GET['read'])){
    $id = (int)$_GET['read'];
    $conn->query("UPDATE messages SET status='read' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// SEARCH (SAFE)
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

$stmt = $conn->prepare("SELECT * FROM messages WHERE name LIKE ? ORDER BY id DESC");
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>
<link rel="stylesheet" href="<?= '/pradip/style.css' ?>">
<section class="admin-page">
<div style="margin-top:10px;">
<h1>Messages</h1>

<form method="GET">
<input type="text" name="search" placeholder="Search name..." value="<?= htmlspecialchars($search) ?>">
<button class="btn">Search</button>
</form>

<table class="admin-table">
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= $row['status'] ?? 'N/A' ?></td>

<td>
<a href="?read=<?= $row['id'] ?>">Mark Read</a> |

<form method="POST" style="display:inline;">
<input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
<button onclick="return confirm('Delete?')">Delete</button>
</form>

</td>
</tr>
<?php endwhile; ?>

</table>

</section>

<?php include '../layout/footer.php'; ?>