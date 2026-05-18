<?php

session_start();
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    createJsonResponse(['ok' => false, 'error' => 'Method not allowed'], 405);
}

if (!isset($_SESSION['user_id'])) {
    createJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
}

$id = $_GET['id'] ?? null;
if (!$id) {
    createJsonResponse(['ok' => false, 'error' => 'Member id required'], 400);
}

$database = new Database();
$db = $database->connect();

$stmt = $db->prepare('SELECT workspace_id, user_id FROM workspace_members WHERE id = ?');
$stmt->execute([$id]);
$membership = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$membership) {
    createJsonResponse(['ok' => false, 'error' => 'Member not found'], 404);
}

if ($membership['user_id'] == $_SESSION['user_id']) {
    createJsonResponse(['ok' => false, 'error' => 'You cannot remove yourself'], 403);
}

$stmt = $db->prepare('SELECT owner_id FROM workspaces WHERE id = ?');
$stmt->execute([$membership['workspace_id']]);
$workspace = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$workspace || $workspace['owner_id'] != $_SESSION['user_id']) {
    createJsonResponse(['ok' => false, 'error' => 'Only workspace owners may remove members'], 403);
}

$delete = $db->prepare('DELETE FROM workspace_members WHERE id = ?');
$delete->execute([$id]);

createJsonResponse(['ok' => true]);
