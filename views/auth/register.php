<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:520px; margin: 0 auto;">
    <h1 class="page-title">Register</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert" role="alert">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=register" class="form-card">
        <div class="form-group">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" placeholder="Name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Password" required>
        </div>
        <button class="button button-primary" type="submit">Register</button>
    </form>

    <p style="margin-top:1rem;">Already have an account? <a href="index.php?action=login">Login</a></p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>