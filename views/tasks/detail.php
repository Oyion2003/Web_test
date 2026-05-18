<?php
$breadcrumbs = [
    ['title' => 'Workspace Home', 'url' => 'index.php?action=workspace_home'],
    ['title' => 'Projects', 'url' => 'index.php?action=projects'],
    ['title' => htmlspecialchars($project['name']), 'url' => 'index.php?action=project_show&id=' . $project['id']],
    ['title' => htmlspecialchars($task['title'])],
];
require_once __DIR__ . '/../layouts/header.php';
?>

<?php if ($task): ?>
    <div class="section-header">
        <div>
            <h1 class="page-title"><?= htmlspecialchars($task['title']) ?></h1>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center; margin-top:0.75rem;">
                <span class="badge-status">Status: <?= htmlspecialchars(ucfirst($task['status'])) ?></span>
                <span class="badge-status">Priority: <?= htmlspecialchars(ucfirst($task['priority'])) ?></span>
                <span class="badge-status">Due: <?= htmlspecialchars($task['due_date']) ?></span>
            </div>
        </div>
        <a class="button button-secondary" href="index.php?action=project_show&id=<?= $project['id'] ?>">Back to Project</a>
    </div>

    <div class="card card-muted">
        <p><strong>Project:</strong> <?= htmlspecialchars($project['name']) ?></p>
        <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
    </div>

    <div class="section-header" style="margin-top:1.5rem;">
        <h2>Comments</h2>
    </div>
    <div id="comment-list" style="display:grid; gap:1rem;">
        <?php if (empty($comments)): ?>
            <div class="card card-muted">No comments yet.</div>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="card card-muted" id="comment-<?= $comment['id'] ?>">
                    <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; align-items:center; margin-bottom:0.5rem;">
                        <strong><?= htmlspecialchars($comment['author_name']) ?></strong>
                        <span style="color:#6b7280; font-size:0.9rem;"><?= htmlspecialchars($comment['created_at']) ?></span>
                    </div>
                    <p style="margin:0 0 0.75rem;"><?= nl2br(htmlspecialchars($comment['body'])) ?></p>
                    <?php if ($comment['user_id'] === $_SESSION['user_id']): ?>
                        <button class="button button-secondary delete-comment" data-id="<?= $comment['id'] ?>">Delete</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form id="comment-form" class="form-card" style="margin-top:20px;">
        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
        <div class="form-group">
            <label for="comment-body">Add a comment</label>
            <textarea id="comment-body" name="body" rows="4" placeholder="Write a comment..."></textarea>
        </div>
        <button class="button button-primary" type="submit">Post Comment</button>
    </form>

    <script>
        const commentForm = document.getElementById('comment-form');
        const commentList = document.getElementById('comment-list');

        commentForm.addEventListener('submit', event => {
            event.preventDefault();
            const body = commentForm.body.value.trim();
            const taskId = <?= json_encode($task['id']) ?>;

            if (!body) return;

            fetch('api/comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_id: taskId, body })
            })
            .then(resp => resp.json())
            .then(data => {
                if (data.ok) {
                    const commentBlock = document.createElement('div');
                    commentBlock.style = 'border:1px solid #e5e7eb; padding:12px; margin-bottom:10px; border-radius:10px;';
                    commentBlock.id = 'comment-' + data.comment.id;

                    const author = document.createElement('strong');
                    author.textContent = data.comment.author_name;

                    const timestamp = document.createElement('span');
                    timestamp.style.color = '#6b7280';
                    timestamp.textContent = data.comment.created_at;

                    const bodyParagraph = document.createElement('p');
                    bodyParagraph.textContent = data.comment.body;

                    const deleteButton = document.createElement('button');
                    deleteButton.className = 'delete-comment';
                    deleteButton.dataset.id = data.comment.id;
                    deleteButton.textContent = 'Delete';

                    commentBlock.appendChild(author);
                    commentBlock.appendChild(timestamp);
                    commentBlock.appendChild(bodyParagraph);
                    commentBlock.appendChild(deleteButton);
                    commentList.appendChild(commentBlock);
                    commentForm.body.value = '';
                } else {
                    alert(data.error || 'Unable to post comment');
                }
            });
        });

        commentList.addEventListener('click', event => {
            if (!event.target.classList.contains('delete-comment')) return;
            const id = event.target.dataset.id;
            if (!confirm('Delete this comment?')) return;

            fetch('api/comments.php?id=' + id, {
                method: 'DELETE'
            })
            .then(resp => resp.json())
            .then(data => {
                if (data.ok) {
                    const el = document.getElementById('comment-' + id);
                    if (el) el.remove();
                } else {
                    alert(data.error || 'Unable to delete comment');
                }
            });
        });
    </script>

<?php else: ?>
    <p>Task not found.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
