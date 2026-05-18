<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <h1 class="page-title">Create Project</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert" role="alert">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=create_project" class="form-card">
        <div class="form-group">
            <label for="name">Project Name</label>
            <input id="name" type="text" name="name" placeholder="Project Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="deadline">Deadline</label>
            <input id="deadline" type="date" name="deadline" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Color</label>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <?php $colors = ['#ef4444' => 'Red', '#3b82f6' => 'Blue', '#22c55e' => 'Green', '#f59e0b' => 'Orange', '#a855f7' => 'Purple']; ?>
                <?php foreach ($colors as $hex => $label): ?>
                    <label style="display:flex; align-items:center; gap:0.45rem;">
                        <input type="radio" name="color_label" value="<?= $hex ?>" <?= (($_POST['color_label'] ?? '#3b82f6') === $hex) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label>Members</label>
            <div style="display:grid; gap:0.75rem;">
                <?php foreach ($members as $m): ?>
                    <label style="display:flex; align-items:center; gap:0.6rem;">
                        <input type="checkbox" name="members[]" value="<?= $m['id'] ?>" <?= in_array($m['id'], $_POST['members'] ?? [], true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($m['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="button button-primary" type="submit">Create Project</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>