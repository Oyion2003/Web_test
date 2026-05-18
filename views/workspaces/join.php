<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2>Join Workspace</h2>

<?php if (!empty($error)): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:10px; margin-bottom:16px; border-radius:6px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="index.php?action=join_workspace">
    <input type="text" name="invite_code" placeholder="Invite Code" required style="width:100%; padding:8px; margin-bottom:12px;" value="<?= htmlspecialchars($_POST['invite_code'] ?? '') ?>"><br>
    <button type="submit" style="padding:10px 16px;">Join Workspace</button>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>