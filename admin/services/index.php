<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* DELETE */

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

/* FETCH */

$result = $conn->query("
    SELECT *
    FROM services
    ORDER BY id DESC
");

include '../layout/header.php';
?>





<style>
    
/* =========================
   SERVICES PAGE (PRO STYLE)
========================= */

.admin-page{
    padding:20px;
}

/* HEADER ROW */
.admin-page > div:first-child{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    flex-wrap:wrap;
    gap:10px;
}

.admin-page h1{
    font-size:24px;
    font-weight:700;
}

/* ================= GRID ================= */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:20px;
}

/* ================= CARD ================= */
.card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px;
    transition:0.3s ease;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}

.card:hover{
    transform:translateY(-6px);
    border-color:var(--primary);
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.card h3{
    font-size:18px;
    margin-bottom:10px;
    color:var(--text);
}

.card p{
    font-size:14px;
    color:var(--muted);
    line-height:1.6;
}

/* ================= BUTTONS ================= */
.btn{
    display:inline-block;
    background:var(--primary);
    color:#fff;
    padding:8px 12px;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
    transition:0.2s;
}

.btn:hover{
    background:#1d4ed8;
    transform:translateY(-2px);
}

/* DELETE BUTTON */
.btn-danger{
    display:inline-block;
    background:#ef4444;
    color:#fff;
    padding:8px 12px;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
    transition:0.2s;
}

.btn-danger:hover{
    background:#dc2626;
    transform:translateY(-2px);
}

/* ================= CARD ACTION AREA ================= */
.card div{
    margin-top:15px;
    display:flex;
    gap:10px;
}

/* ================= RESPONSIVE ================= */
@media(max-width:768px){
    .card div{
        flex-direction:column;
    }
}

</style>



<section class="admin-page">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

        <h1>Services</h1>

        <a href="create.php" class="btn">
            Add Service
        </a>

    </div>

    <div class="grid">

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="card">

                <h3>
                    <?= htmlspecialchars($row['title']) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($row['description']) ?>
                </p>

                <div style="margin-top:15px;display:flex;gap:10px;">

                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn">
                        Edit
                    </a>

                    <a href="?delete=<?= $row['id'] ?>"
                       class="btn-danger"
                       onclick="return confirm('Delete this service?')">
                        Delete
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<?php include '../layout/footer.php'; ?>