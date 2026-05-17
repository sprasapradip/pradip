<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';
require_once '../config/guard.php';

/* =========================
   DELETE PROJECT
========================= */
if(isset($_POST['delete_id'])){

    $id = (int) $_POST['delete_id'];

    // GET IMAGE
    $stmt = $conn->prepare("SELECT image FROM projects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $project = $result->fetch_assoc();

    // DELETE IMAGE
    if(!empty($project['image'])){

        $imagePath = __DIR__ . '/../../uploads/' . $project['image'];

        if(file_exists($imagePath)){
            unlink($imagePath);
        }
    }

    // DELETE PROJECT
    $stmt = $conn->prepare("DELETE FROM projects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* =========================
   FETCH PROJECTS
========================= */
$res = $conn->query("SELECT * FROM projects ORDER BY id DESC");

include __DIR__ . '/../layout/header.php';
?>

<section class="admin-page">

    <h1>Projects</h1>

    <a href="create.php" class="btn">
        Add New Project
    </a>

    <br><br>

    <div class="grid">

        <?php while($row = $res->fetch_assoc()): ?>

            <div class="card">

                <?php if(!empty($row['image'])): ?>

                    <img src="../../uploads/<?= htmlspecialchars($row['image']) ?>"
                         style="
                            width:100%;
                            border-radius:8px;
                            margin-bottom:10px;
                         ">

                <?php endif; ?>

                <h3>
                    <?= htmlspecialchars($row['title']) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($row['description']) ?>
                </p>

                <div style="
                    margin-top:15px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    gap:10px;
                    flex-wrap:wrap;
                ">

                    <a href="edit.php?id=<?= $row['id'] ?>"
                       class="btn">

                        Edit

                    </a>

                    <form method="POST"
                          style="
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            margin:0;
                          ">

                        <input type="hidden"
                               name="delete_id"
                               value="<?= $row['id'] ?>">

                        <button type="submit"
                                class="btn-danger"
                                onclick="return openDeleteModal(this.form)">

                            Delete

                        </button>

                    </form>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<!-- DELETE MODAL -->
<div id="deleteModal" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    z-index:9999;
    justify-content:center;
    align-items:center;
">

    <div style="
        background:#fff;
        padding:30px;
        border-radius:12px;
        width:90%;
        max-width:400px;
        text-align:center;
        box-shadow:0 10px 30px rgba(0,0,0,0.2);
    ">

        <h3 style="margin-bottom:15px;">
            Delete Project
        </h3>

        <p style="margin-bottom:25px;">
            Are you sure you want to delete this project?
        </p>

        <div style="
            display:flex;
            justify-content:center;
            gap:10px;
        ">

            <button onclick="closeDeleteModal()"
                    class="btn">

                Cancel

            </button>

            <button onclick="confirmDelete()"
                    class="btn-danger">

                Delete

            </button>

        </div>

    </div>

</div>

<script>

let deleteForm = null;

function openDeleteModal(form){

    deleteForm = form;

    document.getElementById('deleteModal').style.display = 'flex';

    return false;
}

function closeDeleteModal(){

    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete(){

    if(deleteForm){
        deleteForm.submit();
    }
}

</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>