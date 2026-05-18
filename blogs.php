<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<?php

$stmt = $conn->prepare("
    SELECT *
    FROM blogs
    ORDER BY id DESC
");

$stmt->execute();
$result = $stmt->get_result();

?>

<section class="page">

    <h1 class="page-title">Blogs</h1>

    <p class="text-block">
        Latest articles and engineering updates from field experience.
    </p>

    <div class="project-grid">

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="project-card">

                <?php if(!empty($row['image'])): ?>
                    <div class="project-image">
                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="blog">
                    </div>
                <?php endif; ?>

                <div class="project-content">

                    <h3 class="project-title">
                        <?= htmlspecialchars($row['title']) ?>
                    </h3>

                    <p class="project-description">
                      <?= mb_substr(strip_tags($row['content']), 0, 150) ?>...
                    </p>

                    <a href="blog-single.php?id=<?= $row['id'] ?>" class="btn">
                        Read More
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<?php include 'footer.php'; ?>