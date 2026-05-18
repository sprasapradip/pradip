<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<?php

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT *
    FROM blogs
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if(!$blog){
    die("<h2 style='text-align:center;padding:50px;'>Blog not found</h2>");
}

?>

<section class="page">

    <div style="max-width:800px;margin:auto;text-align:left;">

        <!-- TITLE -->
        <h1 class="page-title">
            <?= htmlspecialchars($blog['title']) ?>
        </h1>

        <!-- DATE -->
        <p style="color:var(--muted);margin-bottom:15px;">
            Published: <?= date('d M Y', strtotime($blog['created_at'])) ?>
        </p>

        <!-- IMAGE -->
        <?php if(!empty($blog['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($blog['image']) ?>"
                 style="width:100%;max-height:420px;object-fit:cover;border-radius:12px;margin-bottom:20px;">
        <?php endif; ?>

     <!-- CONTENT -->
         <div style="line-height:1.9;font-size:16px;color:var(--text);">
          <?= $blog['content']; ?>
         </div>
        <!-- BACK BUTTON -->
        <div style="margin-top:30px;">
            <a href="blogs.php" class="btn">← Back to Blogs</a>
        </div>

    </div>

</section>

<?php include 'footer.php'; ?>