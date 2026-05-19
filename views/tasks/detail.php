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
        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? '' ?>">
        <div class="form-group">
            <label for="comment-body">Add a comment</label>
            <textarea id="comment-body" name="body" rows="4" placeholder="Write a comment..."></textarea>
        </div>
        <button class="button button-primary" type="submit">Post Comment</button>
    </form>

    <script>
        const commentForm = document.getElementById('comment-form');
        const commentList = document.getElementById('comment-list');
        const taskId = <?= json_encode($task['id']) ?>;

        function renderComment(comment) {
            const commentBlock = document.createElement('div');
            commentBlock.className = 'card card-muted';
            commentBlock.id = 'comment-' + comment.id;

            const header = document.createElement('div');
            header.style.display = 'flex';
            header.style.justifyContent = 'space-between';
            header.style.gap = '1rem';
            header.style.flexWrap = 'wrap';
            header.style.alignItems = 'center';
            header.style.marginBottom = '0.5rem';

            const author = document.createElement('strong');
            author.textContent = comment.author_name;

            const timestamp = document.createElement('span');
            timestamp.style.color = '#6b7280';
            timestamp.style.fontSize = '0.9rem';
            timestamp.textContent = comment.created_at;

            header.appendChild(author);
            header.appendChild(timestamp);
            commentBlock.appendChild(header);

            const bodyParagraph = document.createElement('p');
            bodyParagraph.style.margin = '0 0 0.75rem';
            bodyParagraph.textContent = comment.body;
            commentBlock.appendChild(bodyParagraph);

            if (comment.user_id === <?= json_encode($_SESSION['user_id'] ?? null) ?>) {
                const deleteButton = document.createElement('button');
                deleteButton.className = 'button button-secondary delete-comment';
                deleteButton.dataset.id = comment.id;
                deleteButton.textContent = 'Delete';
                commentBlock.appendChild(deleteButton);
            }

            return commentBlock;
        }

        function clearCommentList() {
            commentList.innerHTML = '';
        }

        function showNoComments() {
            clearCommentList();
            const placeholder = document.createElement('div');
            placeholder.className = 'card card-muted';
            placeholder.textContent = 'No comments yet.';
            commentList.appendChild(placeholder);
        }

        function loadComments() {
            fetch('api/comments.php?task_id=' + taskId, {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(resp => resp.json())
            .then(data => {
                if (!data.ok) {
                    console.error('Comment load failed', data.error);
                    return;
                }
                clearCommentList();
                if (!data.comments || !data.comments.length) {
                    showNoComments();
                    return;
                }
                data.comments.forEach(comment => {
                    commentList.appendChild(renderComment(comment));
                });
            })
            .catch(error => {
                console.error('Unable to load comments', error);
            });
        }

        commentForm.addEventListener('submit', event => {
            event.preventDefault();
            const body = commentForm.body.value.trim();
            if (!body) return;

            // Use FormData to ensure cookies and PHP session are handled consistently
            const formData = new FormData(commentForm);
            formData.set('body', body);

            fetch('api/comments.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(resp => resp.text().then(text => {
                let data = null;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    // Non-JSON response (server error) — show raw text for debugging
                    alert(text || 'Unable to post comment');
                    throw new Error('Non-JSON response');
                }

                if (data.ok) {
                    commentForm.body.value = '';
                    loadComments();
                } else {
                    alert(data.error || 'Unable to post comment');
                }
            }))
            .catch(err => {
                if (err && err.message !== 'Non-JSON response') {
                    alert('Unable to post comment');
                }
            });
        });

        commentList.addEventListener('click', event => {
            if (!event.target.classList.contains('delete-comment')) return;
            const id = event.target.dataset.id;
            if (!confirm('Delete this comment?')) return;

            fetch('api/comments.php?id=' + id, {
                method: 'DELETE',
                credentials: 'same-origin'
            })
            .then(resp => resp.json())
            .then(data => {
                if (data.ok) {
                    loadComments();
                } else {
                    alert(data.error || 'Unable to delete comment');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', loadComments);
    </script>

<?php else: ?>
    <p>Task not found.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
