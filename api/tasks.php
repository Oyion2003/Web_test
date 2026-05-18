<?php

session_start();
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    createJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    createJsonResponse(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
$task_id = $payload['task_id'] ?? null;
$new_status = $payload['status'] ?? null;

if (!$task_id || !$new_status) {
    createJsonResponse(['ok' => false, 'error' => 'Task id and status required'], 400);
}

$taskModel = new Task();
$task = $taskModel->find($task_id);
if (!$task) {
    createJsonResponse(['ok' => false, 'error' => 'Task not found'], 404);
}

$projectModel = new Project();
$project = $projectModel->find($task['project_id']);
if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
    createJsonResponse(['ok' => false, 'error' => 'Unauthorized project access'], 403);
}

$validStatuses = ['todo', 'in-progress', 'done'];
$transitions = [
    'todo' => ['in-progress'],
    'in-progress' => ['todo', 'done'],
    'done' => ['in-progress']
];

if (!in_array($new_status, $validStatuses, true) || !in_array($new_status, $transitions[$task['status']] ?? [], true)) {
    createJsonResponse(['ok' => false, 'error' => 'Invalid status transition'], 400);
}

$taskModel->updateStatus($task_id, $new_status);
log_activity($task['project_id'], $_SESSION['user_id'], "Task '{$task['title']}' moved to " . ucfirst(str_replace('-', ' ', $new_status)));
createJsonResponse(['ok' => true, 'new_status' => $new_status]);
