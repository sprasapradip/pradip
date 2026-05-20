<?php include 'config.php'; ?>

<?php

$slug = $_GET['slug'] ?? '';

$stmt = $conn->prepare("
    SELECT *
    FROM blogs
    WHERE slug=?
    AND status='published'
");

$stmt->bind_param("s", $slug);
$stmt->execute();

$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if(!$blog){
    die("
        <div style='
            text-align:center;
            padding:100px 20px;
            font-family:sans-serif;
        '>
            <h2>Blog not found</h2>
        </div>
    ");
}

/* =========================
   UPDATE VIEWS
========================= */

$update = $conn->prepare("
    UPDATE blogs
    SET views = views + 1
    WHERE id=?
");

$update->bind_param("i", $blog['id']);
$update->execute();

$blog['views']++;

/* =========================
   WEBSITE URL
========================= */

$site = "https://pradipsubedi1.com.np";

/* PAGE URL */
$pageUrl = $site . "/blog/" . urlencode($blog['slug']);

/* IMAGE URL */
$imageUrl = !empty($blog['image'])
    ? $site . "/pradip/uploads/" . $blog['image']
    : $site . "/pradip/assets/default-blog.jpg";

/* DESCRIPTION */
$metaDescription = !empty($blog['meta_description'])
    ? $blog['meta_description']
    : mb_substr(
        strip_tags(html_entity_decode($blog['content'])),
        0,
        160
    );

/* SEO VARIABLES */
$page_title = $blog['meta_title'] ?: $blog['title'];
$page_description = $metaDescription;
$page_keywords = $blog['keywords'] ?? '';
$page_image = $imageUrl;
$page_url = $pageUrl;

?>

<?php include 'header.php'; ?>

<section class="page">

    <div style="
        max-width:900px;
        margin:auto;
    ">

        <!-- BLOG CARD -->
        <div style="
            background:var(--card-bg);
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 5px 30px rgba(0,0,0,0.06);
        ">

            <!-- IMAGE -->
            <?php if(!empty($blog['image'])): ?>

                <img
                    src="<?= $imageUrl ?>"
                    alt="<?= htmlspecialchars($blog['title']) ?>"
                    style="
                        width:100%;
                        max-height:500px;
                        object-fit:cover;
                        display:block;
                    "
                >

            <?php endif; ?>

            <!-- CONTENT WRAPPER -->
            <div style="padding:35px;">

                <!-- TITLE -->
                <h1 style="
                    font-size:42px;
                    line-height:1.3;
                    margin-bottom:18px;
                    font-weight:700;
                ">
                    <?= htmlspecialchars($blog['title']) ?>
                </h1>

                <!-- META -->
                <div style="
                    display:flex;
                    gap:18px;
                    flex-wrap:wrap;
                    color:#777;
                    font-size:14px;
                    margin-bottom:35px;
                    border-bottom:1px solid rgba(0,0,0,0.08);
                    padding-bottom:18px;
                ">

                    <span>
                        📅 <?= date('d M Y', strtotime($blog['created_at'])) ?>
                    </span>

                    <span>
                        👁 <?= number_format($blog['views']) ?> Views
                    </span>

                    <?php if(!empty($blog['reading_time'])): ?>

                        <span>
                            ⏱ <?= htmlspecialchars($blog['reading_time']) ?>
                        </span>

                    <?php endif; ?>

                </div>

                <!-- BLOG CONTENT -->
                <div class="blog-content" style="
                    line-height:2;
                    font-size:18px;
                    color:var(--text);
                    text-align:left;
                ">

                    <?= html_entity_decode($blog['content']) ?>

                </div>

                <!-- SHARE -->
                <div style="
                    margin-top:50px;
                    padding-top:30px;
                    border-top:1px solid rgba(0,0,0,0.08);
                ">

                    <h3 style="
                        margin-bottom:18px;
                        font-size:22px;
                    ">
                        Share This Article
                    </h3>

                    <?php
                        $url = urlencode($pageUrl);
                        $title = urlencode($blog['title']);
                    ?>

                    <div style="
                        display:flex;
                        gap:12px;
                        flex-wrap:wrap;
                    ">

                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u=<?= $url ?>"
                            target="_blank"
                            class="btn"
                        >
                            Facebook
                        </a>

                        <a
                            href="https://twitter.com/intent/tweet?url=<?= $url ?>&text=<?= $title ?>"
                            target="_blank"
                            class="btn"
                        >
                            Twitter
                        </a>

                        <a
                            href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $url ?>"
                            target="_blank"
                            class="btn"
                        >
                            LinkedIn
                        </a>

                        <a
                            href="https://api.whatsapp.com/send?text=<?= $title ?>%20<?= $url ?>"
                            target="_blank"
                            class="btn"
                        >
                            WhatsApp
                        </a>

                    </div>

                </div>

            </div>

        </div>

        <!-- RELATED POSTS -->
        <div style="margin-top:70px;">

            <h2 style="
                margin-bottom:25px;
                font-size:34px;
                font-weight:700;
            ">
                Related Articles
            </h2>

            <div class="project-grid">

                <?php

                $related = $conn->prepare("
                    SELECT *
                    FROM blogs
                    WHERE id != ?
                    AND status='published'
                    ORDER BY RAND()
                    LIMIT 3
                ");

                $related->bind_param("i", $blog['id']);
                $related->execute();

                $related_result = $related->get_result();

                while($r = $related_result->fetch_assoc()):

                    $relatedImage = !empty($r['image'])
                        ? $site . "/pradip/uploads/" . $r['image']
                        : $site . "/pradip/assets/default-blog.jpg";

                ?>

                    <div class="project-card" style="
                        overflow:hidden;
                        border-radius:18px;
                        background:var(--card-bg);
                        box-shadow:0 4px 20px rgba(0,0,0,0.06);
                        transition:0.3s;
                    ">

                        <!-- IMAGE -->
                        <?php if(!empty($r['image'])): ?>

                            <div style="
                                height:220px;
                                overflow:hidden;
                            ">

                                <img
                                    src="<?= $relatedImage ?>"
                                    alt="<?= htmlspecialchars($r['title']) ?>"
                                    style="
                                        width:100%;
                                        height:100%;
                                        object-fit:cover;
                                        transition:0.3s;
                                    "
                                >

                            </div>

                        <?php endif; ?>

                        <!-- CONTENT -->
                        <div style="padding:22px;">

                            <h3 style="
                                font-size:22px;
                                margin-bottom:12px;
                                line-height:1.5;
                            ">
                                <?= htmlspecialchars($r['title']) ?>
                            </h3>

                            <p style="
                                color:#777;
                                line-height:1.8;
                                margin-bottom:20px;
                            ">

                                <?= mb_substr(
                                    strip_tags(
                                        html_entity_decode($r['content'])
                                    ),
                                    0,
                                    110
                                ) ?>...

                            </p>

                            <a
                                href="/blog/<?= urlencode($r['slug']) ?>"
                                class="btn"
                            >
                                Read More →
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        </div>

        <!-- BACK BUTTON -->
        <div style="
            margin-top:60px;
            text-align:center;
        ">

            <a href="/pradip/blogs.php" class="btn">

                ← Back to All Blogs

            </a>

        </div>

    </div>

</section>

<?php include 'footer.php'; ?>