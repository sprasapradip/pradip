<?php include 'header.php'; ?>

<?php
$projects = [
 ["img"=>"images/linkedin1.jpg","title"=>"Cable Car Project","desc"=>"Maulakali Cable Car electrical work"],
 ["img"=>"images/linkedin2.jpg","title"=>"Site Installation","desc"=>"Electrical installation process"],
 ["img"=>"images/linkedin3.jpg","title"=>"Maintenance","desc"=>"System troubleshooting"],
 ["img"=>"images/linkedin4.jpg","title"=>"Safety Engineering","desc"=>"Ensuring safety system"]
];
?>

<section class="page">

    <h1 class="page-title">Projects</h1>

    <p class="text-block">
        Electrical engineering and cable car system projects based on real field experience at Maulakalika Cable Car.
    </p>

    <div class="timeline">

        <?php foreach($projects as $p): ?>
        <div class="card">

            <img src="<?php echo $p['img']; ?>" alt="project">

            <div class="card-body">
                <h3 class="section-title"><?php echo $p['title']; ?></h3>
                <p class="text-block"><?php echo $p['desc']; ?></p>
            </div>

        </div>
        <?php endforeach; ?>

    </div>

</section>

<?php include 'footer.php'; ?>