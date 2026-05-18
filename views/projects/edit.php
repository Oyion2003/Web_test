<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <h1 class="page-title">Edit Project</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert" role="alert">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=project_edit&id=<?= $project['id'] ?>" class="form-card">
        <div class="form-group">
            <label for="name">Project Name</label>
            <input id="name" type="text" name="name" value="<?= htmlspecialchars($project['name']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($project['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="deadline">Deadline</label>
            <input id="deadline" type="date" name="deadline" value="<?= htmlspecialchars($project['deadline']) ?>">
        </div>

        <div class="form-group">
            <label>Color Label</label>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <?php $colors = ['#ef4444' => 'Red', '#3b82f6' => 'Blue', '#22c55e' => 'Green', '#f59e0b' => 'Orange', '#a855f7' => 'Purple']; ?>
                <?php foreach ($colors as $hex => $label): ?>
                    <label style="display:flex; align-items:center; gap:0.45rem;">
                        <input type="radio" name="color_label" value="<?= $hex ?>" <?= $project['color_label'] === $hex ? 'checked' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Project Members</label>
            <div style="display:grid; gap:0.75rem;">
                <?php foreach ($workspaceMembers as $member): ?>
                    <label style="display:flex; align-items:center; gap:0.6rem;">
                        <input type="checkbox" name="members[]" value="<?= $member['id'] ?>" <?= in_array($member['id'], $projectMembers, true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($member['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button class="button button-primary" type="submit">Save Changes</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
