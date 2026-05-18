<?php

require_once 'models/Task.php';
require_once 'models/Project.php';
require_once 'config/helpers.php';

class TaskController {

    public function index() {
        requireLogin();

        $project_id = $_GET['project_id'] ?? null;
        $projectModel = new Project();
        $taskModel = new Task();

        $project = $projectModel->find($project_id);
        if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
            redirect('index.php?action=projects');
        }

        $todoTasks = $taskModel->getTasksByStatus($project_id, 'todo');
        $inProgressTasks = $taskModel->getTasksByStatus($project_id, 'in-progress');
        $doneTasks = $taskModel->getTasksByStatus($project_id, 'done');
        $projectMembers = $projectModel->getProjectMembers($project_id);

        require_once 'views/tasks/index.php';
    }

    public function create() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=projects');
        }

        $project_id = $_POST['project_id'] ?? null;
        $projectModel = new Project();
        $taskModel = new Task();

        $project = $projectModel->find($project_id);
        if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
            redirect('index.php?action=projects');
        }

        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $assigned_to = $_POST['assigned_to'] ?? null;
        $priority = $_POST['priority'] ?? 'low';
        $due_date = $_POST['due_date'] ?? null;
        $errors = [];

        if ($title === '') {
            $errors[] = 'Task title is required.';
        }

        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $errors[] = 'Invalid priority selected.';
        }

        if ($due_date === '') {
            $errors[] = 'Due date is required.';
        }

        if (empty($errors)) {
            $task_id = $taskModel->create([
                'project_id' => $project_id,
                'title' => $title,
                'description' => $description,
                'assigned_to' => $assigned_to,
                'priority' => $priority,
                'due_date' => $due_date,
            ]);

            log_activity($project_id, $_SESSION['user_id'], "Created task '{$title}'");
            redirect('index.php?action=tasks&project_id=' . $project_id);
        }

        $todoTasks = $taskModel->getTasksByStatus($project_id, 'todo');
        $inProgressTasks = $taskModel->getTasksByStatus($project_id, 'in-progress');
        $doneTasks = $taskModel->getTasksByStatus($project_id, 'done');
        $projectMembers = $projectModel->getProjectMembers($project_id);
        $project = $projectModel->find($project_id);

        require_once 'views/tasks/index.php';
    }

    public function move() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=projects');
        }

        $task_id = $_POST['task_id'] ?? null;
        $new_status = $_POST['status'] ?? null;
        $taskModel = new Task();
        $task = $taskModel->find($task_id);

        if (!$task) {
            redirect('index.php?action=projects');
        }

        $projectModel = new Project();
        $project = $projectModel->find($task['project_id']);

        if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
            redirect('index.php?action=projects');
        }

        $validStatuses = ['todo', 'in-progress', 'done'];
        if (!in_array($new_status, $validStatuses, true)) {
            redirect('index.php?action=tasks&project_id=' . $task['project_id']);
        }

        $taskModel->updateStatus($task_id, $new_status);
        log_activity($task['project_id'], $_SESSION['user_id'], "Task '{$task['title']}' moved to {$new_status}");
        redirect('index.php?action=tasks&project_id=' . $task['project_id']);
    }

    public function detail() {
        requireLogin();

        $task_id = $_GET['id'] ?? null;
        $taskModel = new Task();
        $projectModel = new Project();
        require_once 'models/Comment.php';

        $task = $taskModel->find($task_id);
        if (!$task) {
            redirect('index.php?action=projects');
        }

        $project = $projectModel->find($task['project_id']);
        if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
            redirect('index.php?action=projects');
        }

        $commentModel = new Comment();
        $comments = $commentModel->getByTaskId($task_id);

        require_once 'views/tasks/detail.php';
    }
}

