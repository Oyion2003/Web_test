<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="section-header">
    <div>
        <h1 class="page-title">Projects</h1>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center;">
        <a class="button button-primary" href="index.php?action=create_project">+ Create Project</a>
        <a class="button button-secondary" href="index.php?action=workspace_home">Workspace Home</a>
    </div>
</div>

<h3>Active Projects</h3>

<?php if (empty($activeProjects)): ?>
    <div class="card">No active projects yet.</div>
<?php endif; ?>

<div class="project-grid">    <?php foreach ($activeProjects as $p): ?>
        <?php $progress = $p['total_tasks'] > 0 ? round(($p['completed_tasks'] / $p['total_tasks']) * 100) : 0; ?>
        <div class="card card-accent" style="border-left-color: <?= htmlspecialchars($p['color_label']) ?>;">
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
            <p>Deadline: <span style="color:<?= $p['deadline'] && $p['deadline'] < date('Y-m-d') ? '#dc2626' : '#111827' ?>; "><?= htmlspecialchars($p['deadline'] ?: 'No deadline') ?></span></p>
            <span class="badge">Progress: <?= $progress ?>%</span>
            <div style="width:100%; background:#e5e7eb; height:10px; border-radius:999px; margin:0.75rem 0;">
                <div style="width:<?= $progress ?>%; background:var(--success); height:100%; border-radius:999px;"></div>
            </div>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-top:1rem;">
                <a class="button button-secondary" href="index.php?action=project_show&id=<?= $p['id'] ?>">Open</a>
                <a class="button button-secondary" href="index.php?action=project_edit&id=<?= $p['id'] ?>">Edit</a>
                <button class="button button-secondary" type="button" onclick="archiveProject(<?= $p['id'] ?>)">Archive</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<h3 style="margin-top:32px;">Archived Projects</h3>

<?php if (empty($archivedProjects)): ?>
    <p>No archived projects.</p>
<?php endif; ?>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px; margin-top:12px;">
    <?php foreach ($archivedProjects as $p): ?>
        <?php $progress = $p['total_tasks'] > 0 ? round(($p['completed_tasks'] / $p['total_tasks']) * 100) : 0; ?>
        <div style="background:#f3f4f6; border:1px solid #d1d5db; border-left:6px solid <?= htmlspecialchars($p['color_label']) ?>; padding:16px; border-radius:12px;">
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
            <p>Deadline: <span style="color:<?= $p['deadline'] && $p['deadline'] < date('Y-m-d') ? '#dc2626' : '#111827' ?>; "><?= htmlspecialchars($p['deadline'] ?: 'No deadline') ?></span></p>
            <p class="badge">Archived</p>
            <a href="index.php?action=project_show&id=<?= $p['id'] ?>">View</a>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function archiveProject(projectId) {
        if (!confirm('Archive this project?')) {
            return;
        }

        fetch('api/projects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=archive&id=' + encodeURIComponent(projectId)
        })
        .then(resp => resp.json())
        .then(data => {
            if (data.ok) {
                location.reload();
            } else {
                alert(data.error || 'Unable to archive project');
            }
        });
    }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>