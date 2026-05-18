<?php
$breadcrumbs = [
    ['title' => 'Workspace Home', 'url' => 'index.php?action=workspace_home'],
    ['title' => 'Projects', 'url' => 'index.php?action=projects'],
    ['title' => htmlspecialchars($project['name'])],
];
require __DIR__ . '/../layouts/header.php';
?>

<div class="section-header">
    <div>
        <h1 class="page-title"><?= htmlspecialchars($project['name']) ?></h1>
        <p class="badge-status">Project details and progress overview</p>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <a class="button button-primary" href="index.php?action=tasks&project_id=<?= $project['id'] ?>">Task Board</a>
        <a class="button button-secondary" href="index.php?action=project_edit&id=<?= $project['id'] ?>">Edit Project</a>
        <a class="button button-secondary" href="index.php?action=project_activity&id=<?= $project['id'] ?>">Activity Feed</a>
    </div>
</div>

<div class="card card-muted">
    <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
    <p><strong>Deadline:</strong> <span style="color:<?= $project['deadline'] && $project['deadline'] < date('Y-m-d') ? '#dc2626' : '#111827' ?>; "><?= htmlspecialchars($project['deadline'] ?: 'No deadline') ?></span></p>
</div>

<div class="section-header" style="margin-top:1.5rem; gap:1rem;">
    <h3>Task Summary</h3>
    <div>
        <span class="badge">To Do: <?= $statusCounts['todo'] ?></span>
        <span class="badge">In Progress: <?= $statusCounts['in-progress'] ?></span>
        <span class="badge">Done: <?= $statusCounts['done'] ?></span>
    </div>
</div>

<div class="card">
    <h3>Members</h3>
    <ul style="margin:0; padding-left:1.25rem;">
        <?php foreach ($assignedCounts as $member): ?>
            <li><?= htmlspecialchars($member['name']) ?> — <?= $member['task_count'] ?> tasks</li>
        <?php endforeach; ?>
    </ul>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>