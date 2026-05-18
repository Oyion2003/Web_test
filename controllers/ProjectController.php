<?php

require_once "models/Project.php";
require_once "models/Task.php";
require_once "models/ActivityLog.php";
require_once "config/helpers.php";

class ProjectController {

    public function index() {
        requireLogin();

        if (empty($_SESSION['workspace_id'])) {
            redirect('index.php?action=workspace_home');
        }

        $projectModel = new Project();
        $activeProjects = $projectModel->getProjects($_SESSION['workspace_id']);
        $archivedProjects = $projectModel->getArchivedProjects($_SESSION['workspace_id']);

        require_once "views/projects/index.php";
    }

    public function create() {
        requireLogin();

        if (empty($_SESSION['workspace_id'])) {
            redirect('index.php?action=workspace_home');
        }

        $projectModel = new Project();
        $members = $projectModel->getWorkspaceMembers($_SESSION['workspace_id']);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $deadline = $_POST['deadline'] ?? null;
            $color = $_POST['color_label'] ?? '#60a5fa';
            $selected = $_POST['members'] ?? [];

            if ($name === '') {
                $errors[] = 'Project name is required.';
            }

            if (empty($selected)) {
                $errors[] = 'At least one member must be selected.';
            }

            if (empty($errors)) {
                $project_id = $projectModel->create([
                    'workspace_id' => $_SESSION['workspace_id'],
                    'name' => $name,
                    'description' => $description,
                    'deadline' => $deadline,
                    'color_label' => $color
                ]);

                $projectModel->addMembers($project_id, $selected);
                redirect('index.php?action=projects');
            }
        }

        require_once "views/projects/create.php";
    }

    public function show() {
        requireLogin();

        $id = $_GET['id'] ?? null;
        $projectModel = new Project();
        $taskModel = new Task();

        $project = $projectModel->find($id);
        if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
            redirect('index.php?action=projects');
        }

        $members = $projectModel->getProjectMembers($id);
        $statusCounts = $taskModel->getStatusCounts($id);
        $assignedCounts = $taskModel->getAssignedTaskCounts($id);

        require_once "views/projects/show.php";
    }

    public function edit() {
        requireLogin();

        $id = $_GET['id'] ?? null;
        $projectModel = new Project();

        $project = $projectModel->find($id);
        if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
            redirect('index.php?action=projects');
        }

        $workspaceMembers = $projectModel->getWorkspaceMembers($_SESSION['workspace_id']);
        $projectMembers = $projectModel->getProjectMemberIds($id);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $deadline = $_POST['deadline'] ?? null;
            $color = $_POST['color_label'] ?? '#60a5fa';
            $selected = $_POST['members'] ?? [];

            if ($name === '') {
                $errors[] = 'Project name is required.';
            }

            if (empty($selected)) {
                $errors[] = 'At least one member must be selected.';
            }

            if (empty($errors)) {
                $projectModel->update($id, [
                    'name' => $name,
                    'description' => $description,
                    'deadline' => $deadline,
                    'color_label' => $color
                ]);
                $projectModel->replaceMembers($id, $selected);
                redirect('index.php?action=project_show&id=' . $id);
            }
        }

        require_once "views/projects/edit.php";
    }

    public function archive() {
        requireLogin();

        $id = $_GET['id'] ?? null;
        $projectModel = new Project();
        $project = $projectModel->find($id);

        if ($project && $project['workspace_id'] == $_SESSION['workspace_id']) {
            $projectModel->archive($id);
        }

        redirect('index.php?action=projects');
    }

    public function activity() {
        requireLogin();

        $id = $_GET['id'] ?? null;
        $projectModel = new Project();
        $activityModel = new ActivityLog();

        $project = $projectModel->find($id);
        if (!$project || $project['workspace_id'] != $_SESSION['workspace_id']) {
            redirect('index.php?action=projects');
        }

        $users = $projectModel->getProjectMembers($id);
        require_once "views/projects/activity.php";
    }
}
