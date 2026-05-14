<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config/guard.php';

/* =========================
   DELETE MESSAGE
========================= */
if(isset($_POST['delete_id'])){

    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");

    if($stmt){

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

    }

    header("Location: index.php?deleted=1");
    exit;
}

/* =========================
   MARK AS READ
========================= */
if(isset($_GET['read'])){

    $id = (int) $_GET['read'];

    $stmt = $conn->prepare("UPDATE messages SET `status` = ? WHERE id = ?");

    if($stmt){

        $status = 'read';

        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

    } else {

        die("SQL Error: " . $conn->error);

    }

    header("Location: index.php?success=1");
    exit;
}

/* =========================
   SEARCH
========================= */
$search = trim($_GET['search'] ?? '');
$searchParam = "%{$search}%";

/* =========================
   FETCH MESSAGES
========================= */
$stmt = $conn->prepare("
    SELECT *
    FROM messages
    WHERE name LIKE ?
    ORDER BY id DESC
");

if(!$stmt){
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $searchParam);
$stmt->execute();

$result = $stmt->get_result();

include '../layout/header.php';
?>

<section class="admin-page">

    <div class="page-header">
        <h1>Messages</h1>

        <p class="page-subtitle">
            Manage contact form messages.
        </p>
    </div>

    <!-- SUCCESS MESSAGE -->

    <?php if(isset($_GET['deleted'])): ?>

        <div class="alert alert-danger">
            Message deleted successfully.
        </div>

    <?php endif; ?>

    <?php if(isset($_GET['success'])): ?>

        <div class="alert alert-success">
            Message marked as read.
        </div>

    <?php endif; ?>

    <!-- SEARCH -->

    <form method="GET" class="search-form">

        <input
            type="text"
            name="search"
            placeholder="Search by name..."
            value="<?= htmlspecialchars($search) ?>"
        >

        <button type="submit" class="btn">
            Search
        </button>

    </form>

    <!-- MESSAGE GRID -->

    <div class="grid">

        <?php if($result->num_rows > 0): ?>

            <?php while($row = $result->fetch_assoc()): ?>

                <div class="card <?= (($row['status'] ?? '') === 'read') ? 'read-card' : '' ?>">

                    <h3>
                        <?= htmlspecialchars($row['name']) ?>
                    </h3>

                    <p>
                        <strong>Email:</strong><br>
                        <?= htmlspecialchars($row['email']) ?>
                    </p>

                    <?php if(!empty($row['message'])): ?>

                        <p class="message-box">
                            <?= nl2br(htmlspecialchars($row['message'])) ?>
                        </p>

                    <?php endif; ?>

                    <p>

                        <strong>Status:</strong>

                        <?php if(($row['status'] ?? '') === 'read'): ?>

                            <span class="badge badge-read">
                                Read
                            </span>

                        <?php else: ?>

                            <span class="badge badge-unread">
                                Unread
                            </span>

                        <?php endif; ?>

                    </p>

                    <div class="action-buttons">

                        <!-- MARK READ -->

                        <?php if(($row['status'] ?? '') !== 'read'): ?>

                            <a
                                href="?read=<?= (int)$row['id'] ?>"
                                class="btn"
                            >
                                Mark Read
                            </a>

                        <?php endif; ?>

                        <!-- DELETE -->

                        <form method="POST">

                            <input
                                type="hidden"
                                name="delete_id"
                                value="<?= (int)$row['id'] ?>"
                            >

                            <button
                                type="button"
                                class="btn-danger delete-btn"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-state">
                No messages found.
            </div>

        <?php endif; ?>

    </div>

</section>

<!-- DELETE CONFIRM -->

<script>

document.querySelectorAll('.delete-btn').forEach(button => {

    button.addEventListener('click', function(){

        if(confirm('Are you sure you want to delete this message?')){

            let card = this.closest('.card');

            card.style.opacity = '0.5';

            setTimeout(() => {

                this.closest('form').submit();

            }, 300);
        }
    });

});

</script>

<style>

/* =========================
   PAGE
========================= */

.admin-page{
    padding:30px;
}

.page-header{
    margin-bottom:25px;
}

.page-header h1{
    font-size:32px;
    margin-bottom:5px;
}

.page-subtitle{
    color:#888;
}

/* =========================
   ALERTS
========================= */

.alert{
    padding:12px 16px;
    border-radius:10px;
    margin-bottom:20px;
    font-weight:500;
}

.alert-success{
    background:#d1fae5;
    color:#065f46;
}

.alert-danger{
    background:#fee2e2;
    color:#991b1b;
}

/* =========================
   SEARCH
========================= */

.search-form{
    display:flex;
    gap:10px;
    margin-bottom:30px;
    flex-wrap:wrap;
}

.search-form input{
    flex:1;
    min-width:250px;
    padding:12px;
    border-radius:10px;
    border:1px solid #ccc;
}

/* =========================
   GRID
========================= */

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
}

/* =========================
   CARD
========================= */

.card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
    transition:0.3s;
    border:1px solid #eee;
}

.card:hover{
    transform:translateY(-4px);
}

.read-card{
    opacity:0.85;
}

/* =========================
   MESSAGE BOX
========================= */

.message-box{
    margin:15px 0;
    background:#f8fafc;
    padding:12px;
    border-radius:10px;
    line-height:1.7;
    color:#444;
}

/* =========================
   BADGES
========================= */

.badge{
    display:inline-block;
    padding:5px 10px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

.badge-read{
    background:#dcfce7;
    color:#166534;
}

.badge-unread{
    background:#fee2e2;
    color:#991b1b;
}

/* =========================
   BUTTONS
========================= */

.action-buttons{
    display:flex;
    gap:10px;
    margin-top:15px;
    flex-wrap:wrap;
}

.btn{
    background:#2563eb;
    color:#fff;
    padding:10px 16px;
    border:none;
    border-radius:10px;
    text-decoration:none;
    cursor:pointer;
}

.btn-danger{
    background:#dc2626;
    color:#fff;
    padding:10px 16px;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

/* =========================
   EMPTY
========================= */

.empty-state{
    padding:40px;
    text-align:center;
    background:#fff;
    border-radius:14px;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .admin-page{
        padding:20px;
    }

    .page-header h1{
        font-size:26px;
    }

}

</style>

<?php include '../layout/footer.php'; ?>
```
