<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

/* =========================
   DELETE BLOG
========================= */

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    /* DELETE IMAGE ALSO */
    $img = $conn->prepare("
        SELECT image
        FROM blogs
        WHERE id=?
    ");

    $img->bind_param("i", $id);
    $img->execute();

    $imgResult = $img->get_result()->fetch_assoc();

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
   FETCH BLOGS
========================= */

$result = $conn->query("
    SELECT *
    FROM blogs
    ORDER BY id DESC
");

$totalBlogs = $result->num_rows;

include '../layout/header.php';
?>

<section class="admin-page">

    <!-- PAGE HEADER -->
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        margin-bottom:35px;
    ">

        <div>

            <h1 style="
                margin:0;
                font-size:34px;
            ">
                Blog Management
            </h1>

            <p style="
                color:gray;
                margin-top:8px;
            ">
                Total Blogs: <?= $totalBlogs ?>
            </p>

        </div>

        <div style="
            display:flex;
            gap:12px;
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

    <!-- SUCCESS MESSAGE -->
    <?php if(isset($_GET['deleted'])): ?>

        <div style="
            background:#d1fae5;
            color:#065f46;
            padding:14px 18px;
            border-radius:12px;
            margin-bottom:25px;
        ">
            Blog deleted successfully.
        </div>

    <?php endif; ?>

    <!-- EMPTY -->
    <?php if($totalBlogs == 0): ?>

        <div style="
            background:var(--card-bg);
            padding:50px;
            border-radius:18px;
            text-align:center;
        ">

            <h2>No Blogs Found</h2>

            <p style="color:gray;">
                Create your first blog post.
            </p>

            <a href="create.php"
               class="btn"
               style="margin-top:15px;">
                Create Blog
            </a>

        </div>

    <?php else: ?>

        <!-- GRID -->
        <div class="grid" style="
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
            gap:25px;
        ">

            <?php while($row = $result->fetch_assoc()): ?>

                <?php

                /* SLUG */
                $slug = !empty($row['slug'])
                    ? $row['slug']
                    : strtolower(trim(
                        preg_replace('/[^A-Za-z0-9-]+/', '-', $row['title'])
                    ));

                /* BLOG URL */
                $blog_link = "../../blog/" . urlencode($slug);

                /* IMAGE */
                $image = !empty($row['image'])
                    ? "../../uploads/" . htmlspecialchars($row['image'])
                    : "../../assets/no-image.jpg";

                /* CONTENT */
                $excerpt = mb_substr(
                    strip_tags(html_entity_decode($row['content'])),
                    0,
                    120
                );

                ?>

                <!-- CARD -->
                <div style="
                    background:var(--card-bg);
                    border-radius:18px;
                    overflow:hidden;
                    box-shadow:0 5px 20px rgba(0,0,0,0.06);
                    transition:0.3s;
                    display:flex;
                    flex-direction:column;
                ">

                    <!-- IMAGE -->
                    <div style="
                        height:220px;
                        overflow:hidden;
                    ">

                        <img
                            src="<?= $image ?>"
                            alt="<?= htmlspecialchars($row['title']) ?>"
                            style="
                                width:100%;
                                height:100%;
                                object-fit:cover;
                                display:block;
                            "
                        >

                    </div>

                    <!-- CONTENT -->
                    <div style="
                        padding:22px;
                        flex:1;
                        display:flex;
                        flex-direction:column;
                    ">

                        <!-- TITLE -->
                        <h3 style="
                            margin-top:0;
                            margin-bottom:12px;
                            line-height:1.5;
                            font-size:24px;
                        ">
                            <?= htmlspecialchars($row['title']) ?>
                        </h3>

                        <!-- META -->
                        <div style="
                            display:flex;
                            gap:15px;
                            flex-wrap:wrap;
                            color:gray;
                            font-size:13px;
                            margin-bottom:14px;
                        ">

                            <span>
                                👁 <?= number_format($row['views'] ?? 0) ?>
                            </span>

                            <span>
                                📅 <?= date('d M Y', strtotime($row['created_at'])) ?>
                            </span>

                            <span style="
                                color:
                                <?= $row['status'] === 'published'
                                    ? '#16a34a'
                                    : '#f59e0b'
                                ?>;
                                font-weight:600;
                            ">
                                ● <?= ucfirst($row['status']) ?>
                            </span>

                        </div>

                        <!-- EXCERPT -->
                        <p style="
                            color:#666;
                            line-height:1.8;
                            flex:1;
                        ">
                            <?= $excerpt ?>...
                        </p>

                        <!-- ACTIONS -->
                        <div style="
                            display:flex;
                            gap:10px;
                            flex-wrap:wrap;
                            margin-top:22px;
                        ">

                            <a href="edit.php?id=<?= $row['id'] ?>"
                               class="btn">
                                Edit
                            </a>

                            <a href="<?= $blog_link ?>"
                               target="_blank"
                               class="btn">
                                View
                            </a>

                            <a href="?delete=<?= $row['id'] ?>"
                               class="btn-danger"
                               onclick="return confirm('Delete this blog permanently?')">
                                Delete
                            </a>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>

</section>

<?php include '../layout/footer.php'; ?>