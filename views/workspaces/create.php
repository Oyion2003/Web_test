<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2>Create Workspace</h2>

<?php if (!empty($errors)): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:10px; margin-bottom:16px; border-radius:6px;">
        <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" action="index.php?action=create_workspace">
    <input type="text" name="name" placeholder="Workspace Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" style="width:100%; padding:8px; margin-bottom:12px;"><br>
    <textarea name="description" placeholder="Description" style="width:100%; padding:8px; height:120px; margin-bottom:12px;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea><br>
    <button type="submit" style="padding:10px 16px;">Create Workspace</button>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>