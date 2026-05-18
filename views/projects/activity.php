<?php
$breadcrumbs = [
    ['title' => 'Workspace Home', 'url' => 'index.php?action=workspace_home'],
    ['title' => 'Projects', 'url' => 'index.php?action=projects'],
    ['title' => htmlspecialchars($project['name']), 'url' => 'index.php?action=project_show&id=' . $project['id']],
    ['title' => 'Activity'],
];
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="section-header">
    <div>
        <h1 class="page-title">Activity Feed</h1>
        <p class="badge-status">Project activity history and team updates</p>
    </div>
    <a class="button button-secondary" href="index.php?action=project_show&id=<?= $project['id'] ?>">Back to Project</a>
</div>

<?php if ($project): ?>
    <div class="card card-muted">
        <p><strong>Project:</strong> <?= htmlspecialchars($project['name']) ?></p>
        <div class="form-group" style="margin-top:1rem; max-width:320px;">
            <label for="activity-filter">Filter by member</label>
            <select id="activity-filter">
                <option value="">All members</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="activity-list" style="display:grid; gap:1rem; margin-top:1rem;"></div>

    <script>
        const projectId = <?= json_encode($project['id']) ?>;
        const activityList = document.getElementById('activity-list');
        const filter = document.getElementById('activity-filter');

        function formatAgo(timestamp) {
            const then = new Date(timestamp);
            const now = new Date();
            const diff = Math.floor((now - then) / 1000);
            if (diff < 60) return diff + ' seconds ago';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            return Math.floor(diff / 86400) + ' days ago';
        }

        function loadActivity() {
            const userId = filter.value;
            const query = new URLSearchParams({ project_id: projectId });
            if (userId) query.set('user_id', userId);

            fetch('api/activity.php?' + query.toString())
                .then(resp => resp.json())
                .then(data => {
                    activityList.innerHTML = '';
                    if (!data.length) {
                        activityList.innerHTML = '<div class="card card-muted">No activity yet.</div>';
                        return;
                    }
                    data.forEach(item => {
                        const block = document.createElement('div');
                        block.className = 'card card-muted';

                        const header = document.createElement('div');
                        header.style.display = 'flex';
                        header.style.justifyContent = 'space-between';
                        header.style.alignItems = 'center';
                        header.style.flexWrap = 'wrap';
                        header.style.gap = '0.75rem';

                        const authorGroup = document.createElement('div');
                        authorGroup.style.display = 'flex';
                        authorGroup.style.alignItems = 'center';
                        authorGroup.style.gap = '0.5rem';

                        const avatar = document.createElement('span');
                        avatar.className = 'activity-avatar';
                        avatar.textContent = item.initials || item.user_name.slice(0, 2).toUpperCase();

                        const author = document.createElement('strong');
                        author.textContent = item.user_name;

                        authorGroup.appendChild(avatar);
                        authorGroup.appendChild(author);

                        const time = document.createElement('span');
                        time.style.color = '#6b7280';
                        time.style.fontSize = '0.9rem';
                        time.textContent = formatAgo(item.created_at);

                        const actionText = document.createElement('div');
                        actionText.style.marginTop = '0.75rem';
                        actionText.textContent = item.action_text;

                        header.appendChild(authorGroup);
                        header.appendChild(time);
                        block.appendChild(header);
                        block.appendChild(actionText);
                        activityList.appendChild(block);
                    });
                });
        }

        filter.addEventListener('change', loadActivity);
        loadActivity();
    </script>
<?php else: ?>
    <div class="card card-muted">Project not found.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
