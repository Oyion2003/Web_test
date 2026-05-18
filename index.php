<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Controllers
require_once "controllers/AuthController.php";
require_once "controllers/WorkspaceController.php";
require_once "controllers/ProjectController.php";
require_once "controllers/TaskController.php"; // ✅ IMPORTANT FIX

$action = $_GET['action'] ?? 'login';

// Controller objects
$auth = new AuthController();
$workspace = new WorkspaceController();
$project = new ProjectController();
$task = new TaskController();

switch ($action) {

    // ================= AUTH =================
    case 'register':
        $auth->register();
        break;

    case 'login':
        $auth->login();
        break;

    case 'logout':
        $auth->logout();
        break;

    // ================= WORKSPACE =================
    case 'workspace_home':
        $workspace->home();
        break;

    case 'create_workspace':
        $workspace->create();
        break;

    case 'join_workspace':
        $workspace->join();
        break;

    case 'switch_workspace':
        $workspace->switchWorkspace();
        break;

    // ================= PROJECTS =================
    case 'projects':
        $project->index();
        break;

    case 'create_project':
        $project->create();
        break;

    case 'project_show':
        $project->show();
        break;

    case 'project_edit':
        $project->edit();
        break;

    case 'project_activity':
        $project->activity();
        break;

    case 'archive_project':
        $project->archive();
        break;

    // ================= TASKS (KANBAN) =================
    case 'tasks':
        $task->index();
        break;

    case 'create_task':
        $task->create();
        break;

    case 'move_task':
        $task->move();
        break;

    case 'task_detail':
        $task->detail();
        break;

    case 'workspace_settings':
        $workspace->settings();
        break;

    // ================= DEFAULT =================
    default:
        $auth->login();
        break;
}
