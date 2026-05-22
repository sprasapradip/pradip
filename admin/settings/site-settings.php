<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

/* ================= LOAD SETTINGS ================= */
$settings = [];

$result = $conn->query("SELECT setting_key, setting_value FROM site_settings");

while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}

/* ================= UPDATE SETTINGS ================= */
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    foreach($_POST as $key => $value){

        if($key === 'save') continue;

        $stmt = $conn->prepare("
            UPDATE site_settings 
            SET setting_value = ?
            WHERE setting_key = ?
        ");

        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();
    }

    header("Location: site-settings.php?updated=1");
    exit;
}

include '../layout/header.php';
?>

<section class="admin-page">

<div class="dashboard-header">
    <div>
        <h1>Site Settings</h1>
        <p class="muted">Manage homepage content dynamically</p>
    </div>
</div>

<?php if(isset($_GET['updated'])): ?>
    <div class="alert success">Settings updated successfully</div>
<?php endif; ?>

<form method="POST">

<div class="grid">

    <!-- HERO -->
    <div class="card">
        <h3>Hero Section</h3>

        <label>Title</label>
        <input type="text" name="hero_title"
               value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>">

        <label>Badge</label>
        <input type="text" name="hero_badge"
               value="<?= htmlspecialchars($settings['hero_badge'] ?? '') ?>">

        <label>Description</label>
        <textarea name="hero_text" rows="4"><?= htmlspecialchars($settings['hero_text'] ?? '') ?></textarea>
    </div>

    <!-- ABOUT -->
    <div class="card">
        <h3>About Section</h3>

        <label>About Line 1</label>
        <textarea name="about_text_1" rows="3"><?= htmlspecialchars($settings['about_text_1'] ?? '') ?></textarea>

        <label>About Line 2</label>
        <textarea name="about_text_2" rows="3"><?= htmlspecialchars($settings['about_text_2'] ?? '') ?></textarea>
    </div>

    <!-- STATS -->
    <div class="card">
        <h3>Statistics</h3>

        <label>Years Experience</label>
        <input type="number" name="experience_years"
               value="<?= htmlspecialchars($settings['experience_years'] ?? '') ?>">

        <label>Electrical Repairs</label>
        <input type="number" name="repairs"
               value="<?= htmlspecialchars($settings['repairs'] ?? '') ?>">

        <label>Maintenance Operations</label>
        <input type="number" name="operations"
               value="<?= htmlspecialchars($settings['operations'] ?? '') ?>">

        <label>Reliability (%)</label>
        <input type="number" name="reliability"
               value="<?= htmlspecialchars($settings['reliability'] ?? '') ?>">
    </div>

</div>

<br>

<button type="submit" class="btn">
    Save All Changes
</button>

</form>

</section>

<?php include '../layout/footer.php'; ?>