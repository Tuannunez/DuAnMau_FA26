<tr>
    <td>
        <span class="admin-user-cell">
            <span class="admin-user-avatar" aria-hidden="true">
                <?= htmlspecialchars(strtoupper(substr($user['full_name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
            </span>
            <strong><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>
        </span>
    </td>
    <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
    <?php if ($section !== 'dashboard'): ?>
        <td><?= htmlspecialchars($user['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
    <?php endif; ?>
    <td>
        <span class="admin-role <?= $user['role_name'] === 'admin' ? 'admin-role-admin' : '' ?>">
            <?= htmlspecialchars($user['role_name'], ENT_QUOTES, 'UTF-8') ?>
        </span>
    </td>
    <td>
        <?php if ((int) $user['status'] === 1): ?>
            <span class="admin-status admin-status-active">Hoạt động</span>
        <?php else: ?>
            <span class="admin-status admin-status-locked">Đã khóa</span>
        <?php endif; ?>
    </td>
</tr>
