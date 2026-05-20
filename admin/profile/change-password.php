<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

$pass = $_POST['password'];
$confirm = $_POST['confirm_password'];

if($pass !== $confirm){
    die("Password not matched");
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admins SET password=? LIMIT 1");
$stmt->bind_param("s", $hash);
$stmt->execute();

header("Location: index.php?password=updated");
exit;