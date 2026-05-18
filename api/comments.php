<?php

session_start();
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    createJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $task_id = $payload['task_id'] ?? null;
    $body = trim($payload['body'] ?? '');

    if (!$task_id || $body === '') {
        createJsonResponse(['ok' => false, 'error' => 'Task and comment body required'], 400);
    }

    $taskModel = new Task();
    $task = $taskModel->find($task_id);
    if (!$task) {
        createJsonResponse(['ok' => false, 'error' => 'Task not found'], 404);
    }

    $projectModel = new Project();
    $project = $projectModel->find($task['project_id']);
    if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
        createJsonResponse(['ok' => false, 'error' => 'Unauthorized task access'], 403);
    }

    $commentModel = new Comment();
    $comment_id = $commentModel->create($task_id, $_SESSION['user_id'], $body);
    log_activity($project['id'], $_SESSION['user_id'], "Commented on task '{$task['title']}'");

    $newComment = $commentModel->find($comment_id);
    $db = (new Database())->connect();
    $stmt = $db->prepare('SELECT u.name AS author_name FROM users u WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    createJsonResponse([
        'ok' => true,
        'comment' => [
            'id' => $comment_id,
            'body' => htmlspecialchars($body),
            'created_at' => $newComment['created_at'],
            'author_name' => $author['author_name'],
            'user_id' => $_SESSION['user_id']
        ]
    ]);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        createJsonResponse(['ok' => false, 'error' => 'Comment id required'], 400);
    }

    $commentModel = new Comment();
    $comment = $commentModel->find($id);
    if (!$comment) {
        createJsonResponse(['ok' => false, 'error' => 'Comment not found'], 404);
    }

    if ($comment['user_id'] != $_SESSION['user_id']) {
        createJsonResponse(['ok' => false, 'error' => 'Forbidden'], 403);
    }

    $taskModel = new Task();
    $task = $taskModel->find($comment['task_id']);
    if (!$task) {
        createJsonResponse(['ok' => false, 'error' => 'Task not found'], 404);
    }

    $projectModel = new Project();
    $project = $projectModel->find($task['project_id']);
    if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
        createJsonResponse(['ok' => false, 'error' => 'Unauthorized task access'], 403);
    }

    $commentModel->delete($id);
    log_activity($project['id'], $_SESSION['user_id'], "Deleted comment on task '{$task['title']}'");
    createJsonResponse(['ok' => true]);
}

createJsonResponse(['ok' => false, 'error' => 'Method not allowed'], 405);
