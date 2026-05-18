<?php
define('APP_INIT', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/guard.php';

if(isset($_POST['save'])){

    $mode = $_POST['maintenance_mode'];
    $notice = $_POST['maintenance_notice'];

    $stmt1 = $conn->prepare(
        "UPDATE settings SET setting_value=? WHERE setting_key='maintenance_mode'"
    );

    $stmt1->bind_param("s", $mode);
    $stmt1->execute();

    $stmt2 = $conn->prepare(
        "UPDATE settings SET setting_value=? WHERE setting_key='maintenance_notice'"
    );

    $stmt2->bind_param("s", $notice);
    $stmt2->execute();

    $success = "Maintenance settings updated successfully.";
}

$mode = $conn->query(
    "SELECT setting_value FROM settings WHERE setting_key='maintenance_mode'"
)->fetch_assoc();

$notice = $conn->query(
    "SELECT setting_value FROM settings WHERE setting_key='maintenance_notice'"
)->fetch_assoc();

include __DIR__ . '/layout/header.php';
?>

<title>Maintenance Settings</title>

<div class="container">

    <h1>Maintenance Settings</h1>

    <?php if(isset($success)): ?>
        <div class="success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Maintenance Mode</label>

        <select name="maintenance_mode">
            <option value="off" <?php if($mode['setting_value']=='off') echo 'selected'; ?>>OFF</option>
            <option value="on" <?php if($mode['setting_value']=='on') echo 'selected'; ?>>ON</option>
        </select>

        <label>Maintenance Notice</label>

        <textarea name="maintenance_notice"><?php echo htmlspecialchars($notice['setting_value']); ?></textarea>

        <button type="submit" name="save">
            Save Settings
        </button>

    </form>

</div>
<?php include __DIR__ . '/layout/footer.php'; ?>