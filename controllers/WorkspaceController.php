<?php

require_once 'models/Workspace.php';
require_once 'models/User.php';
require_once 'config/helpers.php';

class WorkspaceController {

    public function home() {
        requireLogin();

        $workspaceModel = new Workspace();
        $workspaces = $workspaceModel->getUserWorkspaces($_SESSION['user_id']);

        require_once 'views/workspaces/home.php';
    }

    public function create() {
        requireLogin();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);

            if (empty($name)) {
                $errors[] = "Workspace name required.";
            }

            if (empty($errors)) {
                $invite_code = strtoupper(substr(md5(rand()), 0, 6));
                $workspaceModel = new Workspace();

                $workspace_id = $workspaceModel->create(
                    $name,
                    $description,
                    $_SESSION['user_id'],
                    $invite_code
                );

                $workspaceModel->addMember($workspace_id, $_SESSION['user_id']);
                $_SESSION['workspace_id'] = $workspace_id;

                redirect('index.php?action=workspace_home');
            }
        }

        require_once 'views/workspaces/create.php';
    }

    public function join() {
        requireLogin();

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invite_code = trim($_POST['invite_code']);
            $workspaceModel = new Workspace();
            $workspace = $workspaceModel->findByInviteCode($invite_code);

            if ($workspace) {
                $alreadyMember = $workspaceModel->isMember($workspace['id'], $_SESSION['user_id']);

                if (!$alreadyMember) {
                    $workspaceModel->addMember($workspace['id'], $_SESSION['user_id']);
                }

                $_SESSION['workspace_id'] = $workspace['id'];
                redirect('index.php?action=workspace_home');
            }

            $error = "Invalid invite code.";
        }

        require_once 'views/workspaces/join.php';
    }

    public function settings() {
        requireLogin();

        $workspaceModel = new Workspace();
        $workspace_id = $_SESSION['workspace_id'];
        $workspace = $workspaceModel->getById($workspace_id);

        if (!$workspace || $workspace['owner_id'] !== $_SESSION['user_id']) {
            redirect('index.php?action=workspace_home');
        }

        $members = $workspaceModel->getMembers($workspace_id);

        require_once 'views/workspaces/settings.php';
    }

    public function switchWorkspace() {
        requireLogin();

        $workspace_id = $_GET['id'] ?? null;
        $workspaceModel = new Workspace();

        if ($workspace_id && $workspaceModel->isMember($workspace_id, $_SESSION['user_id'])) {
            $_SESSION['workspace_id'] = $workspace_id;
        }

        redirect('index.php?action=workspace_home');
    }
}
