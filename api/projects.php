<?php

session_start();
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Project.php';

if (!isset($_SESSION['user_id'])) {
    createJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    createJsonResponse(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$action = $_POST['action'] ?? null;
$id = $_POST['id'] ?? null;

if ($action === 'archive' && $id) {
    $projectModel = new Project();
    $project = $projectModel->find($id);
    if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
        createJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 403);
    }

    $projectModel->archive($id);
    createJsonResponse(['ok' => true]);
}

createJsonResponse(['ok' => false, 'error' => 'Invalid action'], 400);
