<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="section-header">
    <div>
        <h1 class="page-title">Workspace Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>.</p>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <a class="button button-primary" href="index.php?action=create_workspace">Create Workspace</a>
        <a class="button button-secondary" href="index.php?action=join_workspace">Join Workspace</a>
        <a class="button button-secondary" href="index.php?action=workspace_settings">Workspace Settings</a>
    </div>
</div>

<h3 style="margin-top:0.5rem;">Your Workspaces</h3>

<?php if (empty($workspaces)): ?>
    <div class="card">You are not a member of any workspace yet.</div>
<?php endif; ?>

<div class="workspace-grid" style="margin-top:1rem;">
    <?php foreach($workspaces as $workspace): ?>
        <div class="card card-muted">
            <h4><?= htmlspecialchars($workspace['name']) ?></h4>
            <p><strong>Invite Code:</strong> <?= htmlspecialchars($workspace['invite_code']) ?></p>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-top:1rem;">
                <a class="button button-secondary" href="index.php?action=switch_workspace&id=<?= $workspace['id'] ?>">Open Workspace</a>
                <a class="button button-secondary" href="index.php?action=projects">Go to Projects</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>