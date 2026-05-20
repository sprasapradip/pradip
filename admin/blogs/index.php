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

<section class="admin-page">

    <!-- HEADER -->
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        margin-bottom:25px;
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
    <div style="
        overflow-x:auto;
        background:var(--card-bg);
        border-radius:18px;
        box-shadow:0 5px 20px rgba(0,0,0,0.05);
    ">

        <table style="
            width:100%;
            border-collapse:collapse;
            min-width:1100px;
        ">

            <!-- TABLE HEAD -->
            <thead style="
                background:#f8fafc;
                border-bottom:1px solid #e5e7eb;
            ">

                <tr>

                    <th style="padding:18px;text-align:left;">
                        SN
                    </th>

                    <th style="padding:18px;text-align:left;">
                        Thumbnail
                    </th>

                    <th style="padding:18px;text-align:left;">
                        Post Title
                    </th>

                    <th style="padding:18px;text-align:left;">
                        Keywords
                    </th>

                    <th style="padding:18px;text-align:left;">
                        Views
                    </th>

                    <th style="padding:18px;text-align:left;">
                        Published Date
                    </th>

                    <th style="padding:18px;text-align:left;">
                        Status
                    </th>

                    <th style="padding:18px;text-align:center;">
                        Actions
                    </th>

                </tr>

            </thead>

            <!-- BODY -->
            <tbody>

                <?php
                $sn = $offset + 1;
                ?>

                <?php while($row = $result->fetch_assoc()): ?>

                    <?php

                    $slug = !empty($row['slug'])
                        ? $row['slug']
                        : strtolower(trim(
                            preg_replace('/[^A-Za-z0-9-]+/', '-', $row['title'])
                        ));

                    $blog_link = "../../blog/" . urlencode($slug);

                    $image = !empty($row['image'])
                        ? "../../uploads/" . htmlspecialchars($row['image'])
                        : "../../assets/no-image.jpg";

                    ?>

                    <tr style="
                        border-bottom:1px solid #f1f5f9;
                        transition:0.3s;
                    ">

                        <!-- SN -->
                        <td style="padding:18px;">
                            <?= $sn++ ?>
                        </td>

                        <!-- IMAGE -->
                        <td style="padding:18px;">

                            <img
                                src="<?= $image ?>"
                                alt=""
                                style="
                                    width:80px;
                                    height:60px;
                                    object-fit:cover;
                                    border-radius:10px;
                                "
                            >

                        </td>

                        <!-- TITLE -->
                        <td style="
                            padding:18px;
                            max-width:320px;
                        ">

                            <div style="
                                font-weight:600;
                                margin-bottom:6px;
                                line-height:1.5;
                            ">
                                <?= htmlspecialchars($row['title']) ?>
                            </div>

                            <div style="
                                color:gray;
                                font-size:13px;
                            ">
                                <?= mb_substr(
                                    strip_tags(
                                        html_entity_decode($row['content'])
                                    ),
                                    0,
                                    70
                                ) ?>...
                            </div>

                        </td>

                        <!-- KEYWORDS -->
                        <td style="
                            padding:18px;
                            max-width:200px;
                            color:#555;
                            font-size:14px;
                        ">

                            <?= !empty($row['keywords'])
                                ? htmlspecialchars(
                                    mb_substr($row['keywords'], 0, 60)
                                )
                                : '-'
                            ?>

                        </td>

                        <!-- VIEWS -->
                        <td style="padding:18px;">

                            👁 <?= number_format($row['views'] ?? 0) ?>

                        </td>

                        <!-- DATE -->
                        <td style="padding:18px;">

                            <?= date(
                                'd M Y',
                                strtotime($row['created_at'])
                            ) ?>

                        </td>

                        <!-- STATUS -->
                        <td style="padding:18px;">

                            <span style="
                                padding:6px 12px;
                                border-radius:30px;
                                font-size:13px;
                                font-weight:600;
                                color:white;
                                background:
                                <?= $row['status'] === 'published'
                                    ? '#16a34a'
                                    : '#f59e0b'
                                ?>;
                            ">

                                <?= ucfirst($row['status']) ?>

                            </span>

                        </td>

                        <!-- ACTIONS -->
                        <td style="
                            padding:18px;
                            text-align:center;
                        ">

                            <div style="
                                display:flex;
                                gap:8px;
                                justify-content:center;
                                flex-wrap:wrap;
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

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

    <!-- PAGINATION -->
    <?php if($totalPages > 1): ?>

        <div style="
            display:flex;
            justify-content:center;
            gap:10px;
            flex-wrap:wrap;
            margin-top:35px;
        ">

            <!-- PREVIOUS -->
            <?php if($page > 1): ?>

                <a href="?page=<?= $page - 1 ?>"
                   class="btn">
                    ← Previous
                </a>

            <?php endif; ?>

            <!-- PAGE NUMBERS -->
            <?php for($i = 1; $i <= $totalPages; $i++): ?>

                <a href="?page=<?= $i ?>"
                   class="<?= $i == $page ? 'btn' : 'btn-secondary' ?>"
                   style="
                        min-width:45px;
                        text-align:center;
                   ">

                    <?= $i ?>

                </a>

            <?php endfor; ?>

            <!-- NEXT -->
            <?php if($page < $totalPages): ?>

                <a href="?page=<?= $page + 1 ?>"
                   class="btn">
                    Next →
                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</section>

<?php include '../layout/footer.php'; ?>