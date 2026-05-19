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

    die("<h2 style='text-align:center;padding:50px;'>Blog not found</h2>");

}

/* UPDATE VIEWS */

$update = $conn->prepare("
    UPDATE blogs
    SET views = views + 1
    WHERE id = ?
");

$update->bind_param("i", $blog['id']);
$update->execute();

?>

<title>
<?= htmlspecialchars($blog['meta_title'] ?: $blog['title']) ?>
</title>

<meta name="description"
      content="<?= htmlspecialchars($blog['meta_description']) ?>">

<meta name="keywords"
      content="<?= htmlspecialchars($blog['keywords']) ?>">

<?php include 'header.php'; ?>

</head>

<body>

<section class="page">

    <div style="max-width:850px;margin:auto;">

        <!-- TITLE -->

        <h1 class="page-title">

            <?= htmlspecialchars($blog['title']) ?>

        </h1>

        <!-- META -->

        <div style="
            display:flex;
            gap:15px;
            flex-wrap:wrap;
            color:gray;
            margin-bottom:20px;
            font-size:14px;
        ">

            <span>
                <?= date('d M Y', strtotime($blog['created_at'])) ?>
            </span>

            <span>
                👁 <?= (int)$blog['views'] ?> Views
            </span>

        </div>

        <!-- IMAGE -->

        <?php if(!empty($blog['image'])): ?>

            <img src="uploads/<?= htmlspecialchars($blog['image']) ?>"
                 alt="<?= htmlspecialchars($blog['title']) ?>"
                 style="
                    width:100%;
                    max-height:450px;
                    object-fit:cover;
                    border-radius:14px;
                    margin-bottom:25px;
                 ">

        <?php endif; ?>

        <!-- CONTENT -->

        <div style="
            line-height:1.9;
            font-size:17px;
            color:var(--text);
        ">

            <?= nl2br($blog['content']) ?>

        </div>

        <!-- SHARE -->

        <div style="margin-top:40px;">

            <h3>Share Article</h3>

            <?php
                $url = urlencode("https://yourdomain.com/blog/".$blog['slug']);
                $title = urlencode($blog['title']);
            ?>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">

                <a class="btn"
                   target="_blank"
                   href="https://www.facebook.com/sharer/sharer.php?u=<?= $url ?>">
                    Facebook
                </a>

                <a class="btn"
                   target="_blank"
                   href="https://twitter.com/intent/tweet?url=<?= $url ?>&text=<?= $title ?>">
                    Twitter
                </a>

                <a class="btn"
                   target="_blank"
                   href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $url ?>">
                    LinkedIn
                </a>

            </div>

        </div>

        <!-- RELATED POSTS -->

        <div style="margin-top:60px;">

            <h2>Related Articles</h2>

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
                ?>

                    <div class="project-card">

                        <?php if(!empty($r['image'])): ?>

                            <div class="project-image">

                                <img src="uploads/<?= htmlspecialchars($r['image']) ?>">

                            </div>

                        <?php endif; ?>

                        <div class="project-content">

                            <h3 class="project-title">

                                <?= htmlspecialchars($r['title']) ?>

                            </h3>

                            <a href="/blog/<?= $r['slug'] ?>" class="btn">
                                Read More
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        </div>

        <!-- BACK -->

        <div style="margin-top:50px;">

            <a href="blogs.php" class="btn">

                ← Back to Blogs

            </a>

        </div>

    </div>

</section>

<?php include 'footer.php'; ?>