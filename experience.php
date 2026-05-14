<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<?php

$stmt = $conn->prepare("
    SELECT *
    FROM experience
    ORDER BY id DESC
");

$stmt->execute();
$result = $stmt->get_result();

?>

<section class="page">

    <h2 class="page-title">Experience</h2>

    <div class="timeline">

        <?php if($result->num_rows > 0): ?>

            <?php while($row = $result->fetch_assoc()): ?>

                <div class="timeline-item">

                    <h3>
                        <?= htmlspecialchars($row['title']) ?>
                    </h3>

                    <p class="company">
                        <?= htmlspecialchars($row['company']) ?>
                    </p>

                    <p>
                        <?= nl2br(htmlspecialchars($row['description'])) ?>
                    </p>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No experience added yet.</p>

        <?php endif; ?>

    </div>

</section>

<?php include 'footer.php'; ?>