<?php include 'header.php'; ?>

<?php
// ==========================
// PAGINATION SETTINGS
// ==========================
$limit = 10;

// Current page
$page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? (int)$_GET['page']
    : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

// ==========================
// TOTAL PROJECT COUNT
// ==========================
$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM projects");
$totalData = $totalQuery->fetch_assoc();

$totalProjects = $totalData['total'];
$totalPages = ceil($totalProjects / $limit);

// ==========================
// FETCH PROJECTS
// ==========================
$stmt = $conn->prepare("
    SELECT * 
    FROM projects 
    ORDER BY id DESC 
    LIMIT ? OFFSET ?
");

$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();

$result = $stmt->get_result();
?>

<section class="page">

        <h1 class="page-title">Projects</h1>

        <p class="text-block">
            Electrical engineering, power systems, and cable car maintenance projects based on real field experience.
        </p>

    <div class="project-grid">

        <?php if($result->num_rows > 0): ?>

            <?php while($row = $result->fetch_assoc()): ?>

                <div class="project-card">

                    <?php if(!empty($row['image'])): ?>

                        <div class="project-image">
                            <img 
                                src="uploads/<?= htmlspecialchars($row['image']) ?>" 
                                alt="<?= htmlspecialchars($row['title']) ?>"
                            >
                        </div>

                    <?php endif; ?>

                    <div class="project-content">

                        <h3 class="project-title">
                            <?= htmlspecialchars($row['title']) ?>
                        </h3>

                        <p class="project-description">
                            <?= nl2br(htmlspecialchars($row['description'])) ?>
                        </p>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="no-project">
                No projects found.
            </p>

        <?php endif; ?>

    </div>

    <!-- PAGINATION -->
    <?php if($totalPages > 1): ?>

        <div class="pagination">

            <!-- Previous -->
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="page-btn">
                    ← Prev
                </a>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php for($i = 1; $i <= $totalPages; $i++): ?>

                <a 
                    href="?page=<?= $i ?>" 
                    class="page-btn <?= ($page == $i) ? 'active' : '' ?>"
                >
                    <?= $i ?>
                </a>

            <?php endfor; ?>

            <!-- Next -->
            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="page-btn">
                    Next →
                </a>
            <?php endif; ?>

        </div>

    <?php endif; ?>

</section>

<?php include 'footer.php'; ?>
```
