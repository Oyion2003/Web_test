<?php

require_once "models/User.php";
require_once "models/Workspace.php";
require_once "config/helpers.php";

class AuthController {

    public function register() {

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            if ($name === "") {
                $errors[] = "Name required.";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Valid email required.";
            }

            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters.";
            }

            if (empty($errors)) {
                $userModel = new User();

                if ($userModel->emailExists($email)) {
                    $errors[] = "Email is already registered.";
                } else {
                    $result = $userModel->register($name, $email, $password);

                    if ($result) {
                        $user = $userModel->getByEmail($email);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];

                        $workspaceModel = new Workspace();
                        $firstWorkspace = $workspaceModel->getFirstWorkspaceForUser($user['id']);

                        $_SESSION['workspace_id'] = $firstWorkspace ? $firstWorkspace['id'] : null;

                        redirect('index.php?action=workspace_home');
                    } else {
                        $errors[] = "Registration failed. Please try again.";
                    }
                }
            }
        }

        require_once "views/auth/register.php";
    }

    public function login() {

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            $userModel = new User();
            $user = $userModel->getByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];

                $workspaceModel = new Workspace();
                $firstWorkspace = $workspaceModel->getFirstWorkspaceForUser($user['id']);
                $_SESSION['workspace_id'] = $firstWorkspace ? $firstWorkspace['id'] : null;

                redirect('index.php?action=workspace_home');
            }

            $error = "Invalid credentials.";
        }

        require_once "views/auth/login.php";
    }

    public function logout() {
        session_destroy();
        redirect('index.php?action=login');
    }
}
