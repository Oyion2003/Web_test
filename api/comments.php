<?php

session_start();
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../config/database.php';

$rawInput = file_get_contents('php://input');

// Note: do not reject here; handle auth per-method to allow a localhost dev fallback.

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $task_id = $_GET['task_id'] ?? null;
    if (!$task_id) {
        createJsonResponse(['ok' => false, 'error' => 'Task id required'], 400);
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
    $comments = $commentModel->getByTaskId($task_id);
    createJsonResponse(['ok' => true, 'comments' => $comments]);
}

if ($method === 'POST') {
    try {
        $payload = json_decode($rawInput, true);
        // Fallback to form-encoded POST (supports older clients or misconfigured fetch)
        if ((!$payload || !is_array($payload)) && !empty($_POST)) {
            $payload = $_POST;
        }

        $task_id = $payload['task_id'] ?? null;
        $body = trim($payload['body'] ?? '');

            // Determine user id: prefer session, otherwise allow dev fallback on localhost when user_id is provided
            $user_id_for_create = null;
            if (isset($_SESSION['user_id'])) {
                $user_id_for_create = $_SESSION['user_id'];
            } else {
                $dev_user = $payload['user_id'] ?? null;
                $host = $_SERVER['HTTP_HOST'] ?? '';
                if ($dev_user && (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false)) {
                    $user_id_for_create = (int)$dev_user;
                }
            }

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
            // If session workspace id exists, enforce workspace ownership. Otherwise (dev fallback) skip this check.
            if (isset($_SESSION['workspace_id'])) {
                if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
                    createJsonResponse(['ok' => false, 'error' => 'Unauthorized task access'], 403);
                }
            }

            if (!$user_id_for_create) {
                createJsonResponse(['ok' => false, 'error' => 'Unauthorized: no session'], 401);
            }

        $commentModel = new Comment();
        $comment_id = $commentModel->create($task_id, $user_id_for_create, $body);
        log_activity($project['id'], $user_id_for_create, "Commented on task '{$task['title']}'");

        $newComment = $commentModel->find($comment_id);
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT u.name AS author_name FROM users u WHERE u.id = ?');
        $stmt->execute([$user_id_for_create]);
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        createJsonResponse([
            'ok' => true,
            'comment' => [
                'id' => $comment_id,
                'body' => htmlspecialchars($body),
                'created_at' => $newComment['created_at'],
                'author_name' => $author['author_name'],
                'user_id' => $user_id_for_create
            ]
        ]);
    } catch (Exception $e) {
        error_log('Comments API ERROR: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        createJsonResponse(['ok' => false, 'error' => 'Server error while posting comment'], 500);
    }
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
