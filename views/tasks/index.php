<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php if (!empty($errors)): ?>
    <div class="alert" role="alert">
        <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="section-header">
    <div>
        <h1 class="page-title">Kanban Board</h1>
        <p class="badge-status">Project: <?= htmlspecialchars($project['name']) ?></p>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center;">
        <button class="button button-primary" onclick="document.getElementById('new-task-modal').style.display='flex'">+ New Task</button>
        <a class="button button-secondary" href="index.php?action=project_show&id=<?= $project['id'] ?>">Back to Project</a>
    </div>
</div>

<div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:20px;">
    <span class="badge">To Do: <?= count($todoTasks) ?></span>
    <span class="badge">In Progress: <?= count($inProgressTasks) ?></span>
    <span class="badge">Done: <?= count($doneTasks) ?></span>
</div>

<div class="task-board">
    <?php foreach (['todo' => 'To Do', 'in-progress' => 'In Progress', 'done' => 'Done'] as $statusKey => $statusLabel): ?>
        <div class="task-column">
            <h3><?= $statusLabel ?></h3>
            <div style="margin-top:10px;">
                <?php
                    $statusTasks = [
                        'todo' => $todoTasks ?? [],
                        'in-progress' => $inProgressTasks ?? [],
                        'done' => $doneTasks ?? []
                    ];
                    $taskList = $statusTasks[$statusKey] ?? [];
                ?>

                <?php if (empty($taskList)): ?>
                    <p style="color: var(--muted);">No tasks yet.</p>
                <?php endif; ?>

                <?php foreach ($taskList as $task): ?>
                    <?php $dueClass = ($task['due_date'] < date('Y-m-d') && $task['status'] !== 'done') ? 'border-color: #ef4444;' : ''; ?>
                    <div class="task-card" data-task-id="<?= $task['id'] ?>" data-due-date="<?= $task['due_date'] ?>" style="<?= $dueClass ?>">
                        <h4><?= htmlspecialchars($task['title']) ?></h4>
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.9rem; font-size:0.9rem;">
                            <span class="badge-status">Priority: <?= htmlspecialchars(ucfirst($task['priority'])) ?></span>
                            <span class="badge-status">Due: <?= htmlspecialchars($task['due_date']) ?></span>
                            <span class="badge-status">Assignee: <?= htmlspecialchars($task['assignee_name'] ?: 'Unassigned') ?></span>
                        </div>
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                            <?php if ($statusKey !== 'todo'): ?>
                                <form method="POST" action="index.php?action=move_task" style="display:inline-block; margin:0;">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $statusKey === 'in-progress' ? 'todo' : 'in-progress' ?>">
                                    <button class="button button-secondary" type="submit">←</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($statusKey !== 'done'): ?>
                                <form method="POST" action="index.php?action=move_task" style="display:inline-block; margin:0;">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $statusKey === 'todo' ? 'in-progress' : 'done' ?>">
                                    <button class="button button-secondary" type="submit">→</button>
                                </form>
                            <?php endif; ?>
                            <a class="button button-secondary" href="index.php?action=task_detail&id=<?= $task['id'] ?>">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="new-task-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); align-items:center; justify-content:center; padding:24px; z-index:50;">
    <div style="background:#fff; padding:24px; border-radius:16px; width:100%; max-width:560px; position:relative; box-shadow:0 24px 80px rgba(15,23,42,0.25);">
        <button onclick="document.getElementById('new-task-modal').style.display='none'" style="position:absolute; top:16px; right:16px; background:none; border:none; cursor:pointer; font-size:1.5rem; line-height:1;">×</button>
        <h3 style="margin-top:0;">New Task</h3>
        <form method="POST" action="index.php?action=create_task" class="form-card">
            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
            <div class="form-group">
                <label for="task-title">Title</label>
                <input id="task-title" type="text" name="title" required>
            </div>
            <div class="form-group">
                <label for="task-description">Description</label>
                <textarea id="task-description" name="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="task-assigned">Assignee</label>
                <select id="task-assigned" name="assigned_to">
                    <option value="">Unassigned</option>
                    <?php foreach ($projectMembers as $member): ?>
                        <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Priority</label>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <label><input type="radio" name="priority" value="low" checked> Low</label>
                    <label><input type="radio" name="priority" value="medium"> Medium</label>
                    <label><input type="radio" name="priority" value="high"> High</label>
                </div>
            </div>
            <div class="form-group">
                <label for="task-due_date">Due Date</label>
                <input id="task-due_date" type="date" name="due_date" required>
            </div>
            <button class="button button-primary" type="submit">Create Task</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
