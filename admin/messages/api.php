<?php
define('APP_INIT', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

/* ================= FETCH MESSAGES ================= */
if($action === 'fetch'){

    $stmt = $conn->prepare("
        SELECT id, name, email, message, status, created_at
        FROM messages
        ORDER BY id DESC
        LIMIT 30
    ");

    $stmt->execute();
    $res = $stmt->get_result();

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}

/* ================= MARK AS READ ================= */
if($action === 'read'){

    $id = (int)($_POST['id'] ?? 0);

    $stmt = $conn->prepare("UPDATE messages SET status='read' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(['success'=>true]);
    exit;
}

/* ================= DELETE ================= */
if($action === 'delete'){

    $id = (int)($_POST['id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM messages WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(['success'=>true]);
    exit;
}