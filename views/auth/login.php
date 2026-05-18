<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:520px; margin: 0 auto;">
    <h1 class="page-title">Login</h1>

    <?php if (!empty($error)): ?>
        <div class="alert" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=login" class="form-card">
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Password" required>
        </div>
        <button class="button button-primary" type="submit">Login</button>
    </form>

    <p style="margin-top:1rem;">Don't have an account? <a href="index.php?action=register">Register</a></p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>