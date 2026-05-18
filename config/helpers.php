<?php

require_once __DIR__ . '/database.php';

function redirect($url) {
    header("Location: {$url}");
    exit;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('index.php?action=login');
    }
}

function createJsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function userInitials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $initials = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= strtoupper($part[0]);
        }
    }

    return substr($initials, 0, 2);
}

function log_activity($project_id, $user_id, $action_text) {
    try {
        $database = new Database();
        $db = $database->connect();

        $sql = "INSERT INTO activity_logs (project_id, user_id, action_text) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$project_id, $user_id, $action_text]);
    } catch (PDOException $e) {
        error_log('Activity log failed: ' . $e->getMessage());
    }
}
