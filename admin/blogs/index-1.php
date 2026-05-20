<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* =========================
   DELETE BLOG
========================= */

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    /* GET IMAGE */
    $img = $conn->prepare("
        SELECT image
        FROM blogs
        WHERE id=?
    ");

    $img->bind_param("i", $id);
    $img->execute();

    $imgResult = $img->get_result()->fetch_assoc();

    /* DELETE IMAGE */
    if(!empty($imgResult['image'])){

        $imagePath = "../../uploads/" . $imgResult['image'];

        if(file_exists($imagePath)){
            unlink($imagePath);
        }
    }

    /* DELETE BLOG */
    $stmt = $conn->prepare("
        DELETE FROM blogs
        WHERE id=?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php?deleted=1");
    exit;
}

/* =========================
   UPDATE STATUS
========================= */

if(isset($_POST['update_status'])){

    $id = (int) $_POST['id'];

    $status = $_POST['status'];

    if(in_array($status, ['published', 'draft'])){

        $stmt = $conn->prepare("
            UPDATE blogs
            SET status=?
            WHERE id=?
        ");

        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }

    header("Location: index.php");
    exit;
}

/* =========================
   PAGINATION
========================= */

$limit = 10;

$page = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

/* =========================
   TOTAL BLOGS
========================= */

$totalResult = $conn->query("
    SELECT COUNT(*) as total
    FROM blogs
");

$totalBlogs = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalBlogs / $limit);

/* =========================
   FETCH BLOGS
========================= */

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

<style>

.blog-wrapper{
    background:#fff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 5px 25px rgba(0,0,0,0.05);
}

.table-responsive{
    overflow-x:auto;
}

.blog-table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
}

.blog-table th{
    background:#f8fafc;
    color:#64748b;
    font-size:13px;
    font-weight:600;
    padding:14px 16px;
    text-align:left;
    border-bottom:1px solid #e2e8f0;
    white-space:nowrap;
}

.blog-table td{
    padding:14px 16px;
    border-bottom:1px solid #f1f5f9;
    vertical-align:middle;
    font-size:14px;
}

.blog-table tr{
    transition:0.2s;
}

.blog-table tr:hover{
    background:#fafafa;
}

.post-title{
    font-weight:600;
    color:#111827;
    line-height:1.5;
    max-width:420px;
}

/* STATUS */

.status-select{
    padding:8px 12px;
    border-radius:10px;
    border:1px solid #dbeafe;
    background:white;
    font-size:13px;
    outline:none;
    cursor:pointer;
    min-width:120px;
}

.status-select:focus{
    border-color:#2563eb;
}

/* ACTIONS */

.action-dropdown{
    position:relative;
    display:inline-block;
}

.action-btn{
    border:none;
    background:#f1f5f9;
    color:#111827;
    padding:9px 14px;
    border-radius:10px;
    cursor:pointer;
    font-size:13px;
    font-weight:600;
    transition:0.3s;
}

.action-btn:hover{
    background:#e2e8f0;
}

.action-menu{
    position:absolute;
    top:110%;
    right:0;
    min-width:180px;
    background:white;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    opacity:0;
    visibility:hidden;
    transform:translateY(10px);
    transition:0.25s;
    z-index:999;
}

.action-dropdown:hover .action-menu{
    opacity:1;
    visibility:visible;
    transform:translateY(0);
}

.action-menu a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:13px 16px;
    text-decoration:none;
    color:#111827;
    font-size:14px;
    transition:0.2s;
}

.action-menu a:hover{
    background:#f8fafc;
}

.action-menu .preview{
    color:#2563eb;
}

.action-menu .edit{
    color:#16a34a;
}

.action-menu .delete{
    color:#dc2626;
}

/* PAGINATION */

.pagination{
    display:flex;
    justify-content:center;
    gap:8px;
    flex-wrap:wrap;
    margin-top:30px;
}

.pagination a{
    padding:10px 15px;
    border-radius:10px;
    background:#f1f5f9;
    color:#111827;
    text-decoration:none;
    font-weight:600;
    transition:0.2s;
}

.pagination a:hover{
    background:#e2e8f0;
}

.pagination a.active{
    background:#111827;
    color:white;
}

</style>

<section class="admin-page">

    <!-- HEADER -->
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        margin-bottom:30px;
    ">

        <div>

            <h1 style="
                margin:0;
                font-size:32px;
            ">
                Blog Management
            </h1>

            <p style="
                color:gray;
                margin-top:8px;
            ">
                Total Blogs:
                <?= number_format($totalBlogs) ?>
            </p>

        </div>

        <div style="
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        ">

            <a href="create.php" class="btn">
                + Add Blog
            </a>

            <a href="import.php" class="btn">
                Import Blog
            </a>

        </div>

    </div>

    <!-- SUCCESS -->
    <?php if(isset($_GET['deleted'])): ?>

        <div style="
            background:#dcfce7;
            color:#166534;
            padding:14px 18px;
            border-radius:12px;
            margin-bottom:20px;
        ">

            Blog deleted successfully.

        </div>

    <?php endif; ?>

    <!-- TABLE -->
    <div class="blog-wrapper">

        <div class="table-responsive">

            <table class="blog-table">

                <thead>

                    <tr>

                        <th style="width:70px;">
                            SN
                        </th>

                        <th>
                            Post Title
                        </th>

                        <th style="width:120px;">
                            Views
                        </th>

                        <th style="width:160px;">
                            Published
                        </th>

                        <th style="width:170px;">
                            Status
                        </th>

                        <th style="width:140px;text-align:center;">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php
                    $sn = $offset + 1;
                    ?>

                    <?php while($row = $result->fetch_assoc()): ?>

                        <?php

                        $slug = !empty($row['slug'])
                            ? $row['slug']
                            : strtolower(trim(
                                preg_replace(
                                    '/[^A-Za-z0-9-]+/',
                                    '-',
                                    $row['title']
                                )
                            ));

                        $blog_link = "../../blog/" . urlencode($slug);

                        ?>

                        <tr>

                            <!-- SN -->
                            <td>

                                <?= $sn++ ?>

                            </td>

                            <!-- TITLE -->
                            <td>

                                <div class="post-title">

                                    <?= htmlspecialchars($row['title']) ?>

                                </div>

                            </td>

                            <!-- VIEWS -->
                            <td>

                                👁
                                <?= number_format($row['views'] ?? 0) ?>

                            </td>

                            <!-- DATE -->
                            <td>

                                <?= date(
                                    'd M Y',
                                    strtotime($row['created_at'])
                                ) ?>

                            </td>

                            <!-- STATUS -->
                            <td>

                                <form method="POST">

                                    <input type="hidden"
                                           name="update_status"
                                           value="1">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= $row['id'] ?>">

                                    <select name="status"
                                            onchange="this.form.submit()"
                                            class="status-select">

                                        <option value="published"
                                            <?= $row['status'] == 'published'
                                                ? 'selected'
                                                : ''
                                            ?>>

                                            Published

                                        </option>

                                        <option value="draft"
                                            <?= $row['status'] == 'draft'
                                                ? 'selected'
                                                : ''
                                            ?>>

                                            Draft

                                        </option>

                                    </select>

                                </form>

                            </td>

                            <!-- ACTIONS -->
                            <td style="text-align:center;">

                                <div class="action-dropdown">

                                    <button class="action-btn">

                                        Manage ⌄

                                    </button>

                                    <div class="action-menu">

                                        <!-- PREVIEW -->
                                        <a href="<?= $blog_link ?>"
                                           target="_blank"
                                           class="preview">

                                            👁 Preview

                                        </a>

                                        <!-- EDIT -->
                                        <a href="edit.php?id=<?= $row['id'] ?>"
                                           class="edit">

                                            ✏ Edit

                                        </a>

                                        <!-- DELETE -->
                                        <a href="?delete=<?= $row['id'] ?>"
                                           class="delete"
                                           onclick="return confirm('Delete this blog permanently?')">

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

    </div>

    <!-- PAGINATION -->
    <?php if($totalPages > 1): ?>

        <div class="pagination">

            <!-- PREVIOUS -->
            <?php if($page > 1): ?>

                <a href="?page=<?= $page - 1 ?>">

                    ← Prev

                </a>

            <?php endif; ?>

            <!-- PAGE NUMBERS -->
            <?php for($i = 1; $i <= $totalPages; $i++): ?>

                <a href="?page=<?= $i ?>"
                   class="<?= $i == $page ? 'active' : '' ?>">

                    <?= $i ?>

                </a>

            <?php endfor; ?>

            <!-- NEXT -->
            <?php if($page < $totalPages): ?>

                <a href="?page=<?= $page + 1 ?>">

                    Next →

                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</section>

<?php include '../layout/footer.php'; ?>