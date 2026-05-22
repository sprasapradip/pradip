<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

if(!isset($_FILES['upload'])){
    echo json_encode(['error' => 'No file']);
    exit;
}

$file = $_FILES['upload'];

$allowed = ['jpg','jpeg','png','webp'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if(!in_array($ext, $allowed)){
    echo json_encode(['error' => 'Invalid file']);
    exit;
}

$name = time().'_'.uniqid().'.'.$ext;

$path = "../../uploads/".$name;

if(move_uploaded_file($file['tmp_name'], $path)){
    echo json_encode([
        'url' => "/uploads/".$name
    ]);
} else {
    echo json_encode(['error' => 'Upload failed']);
}