<?php

session_start();
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/Project.php';

function formatActivityEvents($events) {
    return array_map(function($event) {
        return array_merge($event, ['initials' => userInitials($event['user_name'])]);
    }, $events);
}

if (!isset($_SESSION['user_id'])) {
    createJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
}

$project_id = $_GET['project_id'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$project_id) {
    createJsonResponse(['ok' => false, 'error' => 'Project id required'], 400);
}

$projectModel = new Project();
$project = $projectModel->find($project_id);
if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
    createJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 403);
}

$activityModel = new ActivityLog();
$events = $activityModel->getProjectActivity($project_id, $user_id ? intval($user_id) : null);
createJsonResponse(formatActivityEvents($events));
