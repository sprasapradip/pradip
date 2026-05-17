<?php include 'config.php'; ?>
<?php include 'header.php'; ?>
<?php

$stmt = $conn->prepare("
    SELECT *
    FROM services
    ORDER BY id DESC
");

$stmt->execute();

$result = $stmt->get_result();

?>

<section class="page">

    <h1 class="page-title">Services</h1>

    <p class="text-block">
        Professional electrical engineering services including installation, maintenance, and system troubleshooting.
    </p>

    <div class="service-grid">

        <?php if($result->num_rows > 0): ?>

            <?php while($row = $result->fetch_assoc()): ?>

                <div class="service-card">

                    <h3 class="section-title">
                        <?= htmlspecialchars($row['title']) ?>
                    </h3>

                    <p>
                        <?= nl2br(htmlspecialchars($row['description'])) ?>
                    </p>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No services available.</p>

        <?php endif; ?>

    </div>

</section>

<?php include 'footer.php'; ?>
