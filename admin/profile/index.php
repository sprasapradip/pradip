<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

/* ================= SAFE FETCH ================= */
$result = $conn->query("SELECT * FROM admins LIMIT 1");

if(!$result){
    die("SQL Error: " . $conn->error);
}

$admin = $result->fetch_assoc();

include '../layout/header.php';
?>

<section class="admin-page">

<!-- HEADER -->
<div class="dashboard-header">

    <div>
        <h1>Profile</h1>
        <p class="muted">Manage your admin account details</p>
    </div>

</div>

<!-- PROFILE GRID -->
<div class="profile-grid">

    <!-- LEFT CARD -->
    <div class="card profile-card">

        <div class="avatar-box">

            <div class="avatar-large" style="overflow:hidden;">

<?php if(!empty($admin['image'])): ?>

    <img src="/pradip/uploads/<?= htmlspecialchars($admin['image']) ?>"
         style="width:100%;height:100%;object-fit:cover;">

<?php else: ?>

    <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?>

<?php endif; ?>

</div>

            <h2>
                <?= htmlspecialchars($admin['username'] ?? 'Admin') ?>
            </h2>

            <p class="muted">
                Username: <?= htmlspecialchars($admin['username'] ?? '') ?>
            </p>

        </div>

        <div class="profile-info">

            <p><strong>Role:</strong> Admin</p>

            <p><strong>Status:</strong>
                <span class="badge badge-read">Active</span>
            </p>

            <p><strong>Created:</strong>
                <?= !empty($admin['created_at']) 
                    ? date('d M Y', strtotime($admin['created_at'])) 
                    : '-' ?>
            </p>

        </div>

    </div>

    <!-- RIGHT FORM -->
    <div class="card">

        <h3>Edit Profile</h3>

        <form method="POST" action="update-profile.php" enctype="multipart/form-data">

    <label>Username</label>
    <input type="text" name="username"
           value="<?= htmlspecialchars($admin['username'] ?? '') ?>"
           required>

    <label>Profile Photo</label>
    <input type="file" name="image" accept="image/*">

    <button class="btn" type="submit">
        Save Changes
    </button>

</form>

    </div>

</div>

<!-- PASSWORD SECTION -->
<div class="card password-card">

    <h3>Change Password</h3>

    <form method="POST" action="change-password.php">

        <label>New Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button class="btn-danger" type="submit">
            Update Password
        </button>

    </form>

</div>

</section>

<?php include '../layout/footer.php'; ?>