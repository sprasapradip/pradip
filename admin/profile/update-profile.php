<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

/* ================= INPUT ================= */
$username = $_POST['username'] ?? '';

if(empty($username)){
    die("Username is required");
}

/* ================= IMAGE UPLOAD ================= */
$imageName = null;

if(!empty($_FILES['image']['name'])){

    $targetDir = __DIR__ . '/../../uploads/';

    if(!is_dir($targetDir)){
        mkdir($targetDir, 0777, true);
    }

    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $targetFile = $targetDir . $imageName;

    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
}

/* ================= UPDATE QUERY ================= */
if($imageName){

    $stmt = $conn->prepare("UPDATE admins SET username=?, image=? WHERE id=1");
    if(!$stmt){
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $imageName);

} else {

    $stmt = $conn->prepare("UPDATE admins SET username=? WHERE id=1");
    if(!$stmt){
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
}

$stmt->execute();
$stmt->close();

/* ================= REDIRECT ================= */
header("Location: index.php?updated=1");
exit;