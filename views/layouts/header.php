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
            --bg-alt: #eef2ff;
            --body-bg: linear-gradient(180deg, #f8fbff 0%, #eef2ff 100%);
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
            --button-secondary-bg: #f3f4f6;
            --button-secondary-text: var(--text);
            --button-secondary-hover: #e5e7eb;
            --topbar: rgba(15, 23, 42, 0.95);
            --topbar-text: #fff;
            --topbar-link: #d1d5db;
            --card-muted: #f8fafc;
            --badge-bg: #eef2ff;
            --badge-status-bg: #e2e8f0;
            --input-bg: #fff;
            --input-border: #e5e7eb;
            --table-header: #f8fafc;
            --alert-bg: #fef3f2;
            --alert-border: #fecaca;
            --alert-text: #b91c1c;
            --task-column-bg: #f8fafc;
            --link-color: var(--primary);
        }

        html[data-theme="dark"] {
            --bg: #0b1220;
            --bg-alt: #111827;
            --body-bg: linear-gradient(180deg, #020617 0%, #0f172a 100%);
            --surface: #111827;
            --surface-strong: #1e293b;
            --border: #334155;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --primary: #60a5fa;
            --primary-strong: #38bdf8;
            --success: #34d399;
            --danger: #fda4af;
            --warning: #fbbf24;
            --button-secondary-bg: #1f2937;
            --button-secondary-text: #e2e8f0;
            --button-secondary-hover: rgba(255,255,255,0.12);
            --topbar: rgba(15, 23, 42, 0.95);
            --topbar-text: #e2e8f0;
            --topbar-link: #cbd5e1;
            --card-muted: #0f172a;
            --badge-bg: #1e293b;
            --badge-status-bg: #334155;
            --input-bg: #0f172a;
            --input-border: #334155;
            --table-header: #1f2937;
            --alert-bg: rgba(248, 113, 113, 0.12);
            --alert-border: #fca5a5;
            --alert-text: #fee2e2;
            --task-column-bg: #111827;
            --link-color: #60a5fa;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--body-bg);
            color: var(--text);
            min-height: 100vh;
            transition: background 0.25s ease, color 0.25s ease;
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
            background: var(--button-secondary-bg);
            color: var(--button-secondary-text);
            padding: 0.75rem 1.25rem;
        }

        .button-secondary:hover {
            background: var(--button-secondary-hover);
        }

        .top-bar {
            background: var(--topbar);
            color: var(--topbar-text);
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
            color: var(--topbar-link);
        }

        .top-bar .nav-links a:hover {
            color: #fff;
        }

        .workspace-switcher {
            background: var(--surface);
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
            background: var(--card-muted);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            background: var(--badge-bg);
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
            background: var(--badge-status-bg);
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
            background: var(--surface);
            border: 1px solid var(--border);
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
            background: var(--task-column-bg);
            border: 1px solid var(--border);
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
            border: 1px solid var(--input-border);
            border-radius: 0.85rem;
            padding: 0.9rem 1rem;
            font: inherit;
            color: var(--text);
            background: var(--input-bg);
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
            background: var(--table-header);
            font-weight: 700;
        }

        .alert {
            background: var(--alert-bg);
            color: var(--alert-text);
            border: 1px solid var(--alert-border);
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
            color: var(--link-color);
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
<script>
    function getStoredTheme() {
        const stored = localStorage.getItem('taskhub-theme');
        if (stored === 'light' || stored === 'dark') return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('taskhub-theme', theme);
        const button = document.getElementById('theme-toggle');
        if (button) {
            button.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(getStoredTheme());
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
            });
        }
    });
</script>
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
            <button id="theme-toggle" class="button button-secondary" type="button">Theme</button>
            <span style="color:var(--topbar-link); font-weight:600;">Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
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
