<?php
$breadcrumbs = [
    ['title' => 'Workspace Home', 'url' => 'index.php?action=workspace_home'],
    ['title' => 'Settings'],
];
require __DIR__ . '/../layouts/header.php';
?>

<div class="section-header">
    <div>
        <h1 class="page-title">Workspace Settings</h1>
        <p class="badge-status">Manage workspace info and team members</p>
    </div>
</div>

<?php if ($workspace): ?>
    <div class="card card-muted">
        <p><strong>Name:</strong> <?= htmlspecialchars($workspace['name']) ?></p>
        <p><strong>Invite Code:</strong> <?= htmlspecialchars($workspace['invite_code']) ?></p>
    </div>

    <div class="card" style="overflow-x:auto;">
        <h2>Members</h2>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f3f4f6;">
                    <th style="text-align:left; padding:12px;">Name</th>
                    <th style="text-align:left; padding:12px;">Email</th>
                    <th style="text-align:left; padding:12px;">Joined</th>
                    <th style="text-align:left; padding:12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:12px;"><?= htmlspecialchars($member['name']) ?></td>
                        <td style="padding:12px;"><?= htmlspecialchars($member['email']) ?></td>
                        <td style="padding:12px;"><?= htmlspecialchars($member['joined_at']) ?></td>
                        <td style="padding:12px;">
                            <?php if ($member['id'] !== $_SESSION['user_id']): ?>
                                <button class="button button-secondary remove-member" data-member-id="<?= $member['id'] ?>">Remove</button>
                            <?php else: ?>
                                Owner
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.remove-member').forEach(button => {
            button.addEventListener('click', () => {
                const memberId = button.dataset.memberId;

                if (!confirm('Remove this member from workspace?')) {
                    return;
                }

                fetch('api/workspace-members.php?id=' + memberId, {
                    method: 'DELETE'
                })
                .then(resp => resp.json())
                .then(data => {
                    if (data.ok) {
                        button.closest('tr').remove();
                    } else {
                        alert(data.error || 'Unable to remove member.');
                    }
                });
            });
        });
    </script>
<?php else: ?>
    <div class="card card-muted">Workspace not found.</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
