<?php

define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $id = (int) $_POST['id'];

    $status = $_POST['status'];

    if(in_array($status, ['published', 'draft'])){

        $stmt = $conn->prepare("
            UPDATE blogs
            SET status=?
            WHERE id=?
        ");

        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
}

header("Location: index.php");
exit;
?>