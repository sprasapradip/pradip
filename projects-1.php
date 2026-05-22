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

    <h1 class="page-title">
        Projects
    </h1>

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

                        <?php
                        $plainText = strip_tags($row['description']);
                        $shortText = mb_substr($plainText, 0, 150);
                        ?>

                        <div class="project-description">

                            <?= nl2br(htmlspecialchars($shortText)) ?>...

                        </div>
                      <div class="project-content">

                      <a class="btn"
   href="/pradip/project-single.php?slug=<?= urlencode($row['slug']) ?>">
    Read More
</a>
 
                       </div>
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

<!-- PROJECT MODAL -->
<div id="projectModal" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.7);
    z-index:9999;
    overflow:auto;
">

    <div style="
        background:#fff;
        width:90%;
        max-width:900px;
        margin:50px auto;
        border-radius:12px;
        overflow:hidden;
        position:relative;
    ">

        <!-- CLOSE -->
        <button onclick="closeProjectModal()" style="
            position:absolute;
            top:15px;
            right:15px;
            background:#2563eb;
            color:#fff;
            border:none;
            width:40px;
            height:40px;
            border-radius:50%;
            cursor:pointer;
            font-size:20px;
            z-index:10;
        ">
            ×
        </button>

        <!-- IMAGE -->
        <div id="modalImageWrap"></div>

        <!-- CONTENT -->
        <div style="padding:30px;">

            <h2 id="modalTitle"
                style="
                    margin-bottom:20px;
                    color:#000;
                    font-size:32px;
                    line-height:1.4;
                ">
            </h2>

            <div id="modalDescription"
                 style="
                    line-height:2;
                    font-size:17px;
                    color:#333;
                 ">
            </div>

        </div>

    </div>

</div>

<?php include 'footer.php'; ?>