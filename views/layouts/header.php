<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Workspace.php';

$workspaceModel = new Workspace();
$navWorkspaces = [];
$currentWorkspace = null;

if (isset($_SESSION['user_id'])) {
    $navWorkspaces = $workspaceModel->getUserWorkspaces($_SESSION['user_id']);
    if (isset($_SESSION['workspace_id'])) {
        foreach ($navWorkspaces as $workspace) {
            if ($workspace['id'] === $_SESSION['workspace_id']) {
                $currentWorkspace = $workspace;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Task & Project Management Tool</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --surface: #ffffff;
            --surface-strong: #f8fafc;
            --border: #e5e7eb;
            --text: #111827;
            --muted: #6b7280;
            --primary: #2563eb;
            --primary-strong: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(180deg, #f8fbff 0%, #eef2ff 100%);
            color: var(--text);
            min-height: 100vh;
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            color: var(--primary-strong);
        }

        button,
        input[type="submit"],
        .button,
        .button-primary,
        .button-secondary {
            font: inherit;
            cursor: pointer;
            border: none;
            border-radius: 999px;
            transition: all 0.2s ease;
        }

        .button,
        .button-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            background: var(--primary);
            color: #fff;
        }

        .button-primary:hover,
        button:hover {
            background: var(--primary-strong);
        }

        .button-secondary {
            background: #f3f4f6;
            color: var(--text);
            padding: 0.75rem 1.25rem;
        }

        .button-secondary:hover {
            background: #e5e7eb;
        }

        .top-bar {
            background: rgba(15, 23, 42, 0.95);
            color: #fff;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.18);
        }

        .top-bar .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .top-bar .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .top-bar .nav-links a,
        .top-bar .nav-links span {
            color: #d1d5db;
        }

        .top-bar .nav-links a:hover {
            color: #fff;
        }

        .workspace-switcher {
            background: #fff;
            color: var(--text);
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            outline: none;
            min-width: 200px;
        }

        .app-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 1.5rem 1.25rem 3rem;
        }

        .main-content {
            background: var(--surface);
            border-radius: 1rem;
            padding: 2rem;
            margin-top: 1.5rem;
            box-shadow: 0 24px 72px rgba(15, 23, 42, 0.08);
        }

        .page-title {
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: clamp(1.65rem, 2vw, 2rem);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .section-header h2,
        .section-header h3 {
            margin: 0;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
            padding: 1.25rem;
        }

        .card-accent {
            border-left: 0.5rem solid var(--primary);
        }

        .card-muted {
            background: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            background: #eef2ff;
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: #e2e8f0;
            color: var(--text);
            font-size: 0.82rem;
        }

        .grid {
            display: grid;
            gap: 1.25rem;
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .project-grid,
        .workspace-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
        }

        .task-board {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }

        .task-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.1);
        }

        .task-card.overdue-card {
            border-color: #ef4444;
            box-shadow: 0 0 0 1px rgba(239,68,68,0.12), 0 18px 32px rgba(15, 23, 42, 0.1);
        }

        .task-card h4 {
            margin: 0 0 0.75rem;
            font-size: 1.05rem;
        }
        .activity-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e0e7ff;
            color: #1e40af;
            font-weight: 700;
            font-size: 0.9rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .task-card p,
        .task-card span,
        .task-card div {
            margin: 0.35rem 0;
        }

        .task-column {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            min-height: 360px;
        }

        .task-column h3 {
            margin-top: 0;
            font-size: 1.05rem;
        }

        .form-card {
            display: grid;
            gap: 1rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        label {
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 0.85rem;
            padding: 0.9rem 1rem;
            font: inherit;
            color: var(--text);
            background: #fff;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.9rem 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f8fafc;
            font-weight: 700;
        }

        .alert {
            background: #fef3f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 1rem;
            padding: 1rem;
        }

        .breadcrumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            color: var(--muted);
            margin: 1rem 0 1.5rem;
            font-size: 0.95rem;
        }

        .breadcrumbs a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumbs a:hover {
            text-decoration: underline;
        }

        .footer-note {
            text-align: center;
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 2rem;
        }

        @media (max-width: 880px) {
            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .top-bar .nav-links {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="top-bar">
    <div class="brand">
        <a href="<?= isset($_SESSION['user_id']) ? 'index.php?action=workspace_home' : 'index.php?action=login' ?>">TaskHub</a>
        <?php if ($currentWorkspace): ?>
            <span class="badge">Workspace: <?= htmlspecialchars($currentWorkspace['name']) ?></span>
        <?php endif; ?>
    </div>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="button button-secondary" type="button" onclick="history.back()">Back</button>
            <span style="color:#d1d5db; font-weight:600;">Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="index.php?action=workspace_home">Workspace Home</a>
            <a href="index.php?action=projects">Projects</a>
            <a href="index.php?action=workspace_settings">Workspace Settings</a>
            <?php if (isset($_GET['project_id'])): ?>
                <a href="index.php?action=tasks&project_id=<?= htmlspecialchars($_GET['project_id']) ?>">Current Board</a>
            <?php endif; ?>
            <a href="index.php?action=logout">Logout</a>
        <?php else: ?>
            <a href="index.php?action=login">Login</a>
            <a href="index.php?action=register">Register</a>
        <?php endif; ?>
    </div>
</div>
<div class="app-shell">
    <?php if (!empty($navWorkspaces)): ?>
        <div style="margin-top:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <label for="workspace-switch" style="font-weight:600; color:var(--muted);">Switch workspace:</label>
            <select id="workspace-switch" class="workspace-switcher" onchange="location = this.value">
                <option value="#">Select</option>
                <?php foreach ($navWorkspaces as $workspace): ?>
                    <option value="index.php?action=switch_workspace&id=<?= $workspace['id'] ?>" <?= isset($_SESSION['workspace_id']) && $_SESSION['workspace_id'] == $workspace['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($workspace['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <?php if (!empty($breadcrumbs)): ?>
        <nav class="breadcrumbs">
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <?php if (!empty($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
                    <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['title']) ?></a>
                    <span>&raquo;</span>
                <?php else: ?>
                    <span><?= htmlspecialchars($crumb['title']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
    <div class="main-content">
    <?php if (!empty($navWorkspaces)): ?>
        <div style="margin-top:16px; display:flex; align-items:center; gap:10px;">
            <label for="workspace-switch">Switch workspace:</label>
            <select id="workspace-switch" class="workspace-switcher" onchange="location = this.value">
                <option value="#">Select</option>
                <?php foreach ($navWorkspaces as $workspace): ?>
                    <option value="index.php?action=switch_workspace&id=<?= $workspace['id'] ?>" <?= isset($_SESSION['workspace_id']) && $_SESSION['workspace_id'] == $workspace['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($workspace['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="main-content">
