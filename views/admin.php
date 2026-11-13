<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị | TUAN LFC SHOP</title>
    <link href="<?= BASE_URL ?>assets/admin.css" rel="stylesheet">
</head>

<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="<?= BASE_URL ?>?action=admin">
                <span class="admin-brand-mark">TLC</span>
                <span>TUAN LFC <strong>SHOP</strong><small>ADMIN PANEL</small></span>
            </a>

            <p class="admin-sidebar-label">TỔNG QUAN</p>
            <a class="admin-nav-link is-active" href="<?= BASE_URL ?>?action=admin">
                <span class="admin-nav-icon" aria-hidden="true">⌂</span> Bảng điều khiển
            </a>
            <a class="admin-nav-link" href="#users">
                <span class="admin-nav-icon" aria-hidden="true">♙</span> Tài khoản
            </a>

            <div class="admin-sidebar-bottom">
                <a class="admin-store-link" href="<?= BASE_URL ?>">← Quay lại cửa hàng</a>
                <form method="post" action="<?= BASE_URL ?>?action=logout">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="admin-logout" type="submit">Đăng xuất</button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <span class="admin-eyebrow">TUAN LFC SHOP / ADMIN</span>
                    <h1>Bảng điều khiển</h1>
                </div>
                <div class="admin-profile">
                    <span class="admin-avatar" aria-hidden="true">
                        <?= htmlspecialchars(strtoupper(substr($_SESSION['user']['full_name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span>
                        <strong><?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small>Quản trị viên</small>
                    </span>
                </div>
            </header>

            <section class="admin-content" aria-labelledby="welcome-title">
                <div class="admin-welcome">
                    <div>
                        <p>Xin chào, <?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <h2 id="welcome-title">Tổng quan hệ thống</h2>
                        <span>Quản lý tài khoản và theo dõi tình trạng cửa hàng của bạn.</span>
                    </div>
                    <span class="admin-welcome-mark" aria-hidden="true">LFC</span>
                </div>

                <div class="admin-stats">
                    <article class="admin-stat-card">
                        <span class="admin-stat-icon admin-stat-icon-red" aria-hidden="true">♙</span>
                        <div>
                            <p>Tổng tài khoản</p>
                            <strong><?= number_format((int) $stats['total_users']) ?></strong>
                        </div>
                    </article>
                    <article class="admin-stat-card">
                        <span class="admin-stat-icon admin-stat-icon-green" aria-hidden="true">✓</span>
                        <div>
                            <p>Đang hoạt động</p>
                            <strong><?= number_format((int) $stats['active_users']) ?></strong>
                        </div>
                    </article>
                    <article class="admin-stat-card">
                        <span class="admin-stat-icon admin-stat-icon-amber" aria-hidden="true">!</span>
                        <div>
                            <p>Tài khoản bị khóa</p>
                            <strong><?= number_format((int) $stats['locked_users']) ?></strong>
                        </div>
                    </article>
                    <article class="admin-stat-card">
                        <span class="admin-stat-icon admin-stat-icon-dark" aria-hidden="true">A</span>
                        <div>
                            <p>Quản trị viên</p>
                            <strong><?= number_format((int) $stats['admin_users']) ?></strong>
                        </div>
                    </article>
                </div>

                <section class="admin-panel" id="users" aria-labelledby="users-title">
                    <div class="admin-panel-heading">
                        <div>
                            <h2 id="users-title">Tài khoản mới nhất</h2>
                            <p>10 tài khoản được tạo gần đây</p>
                        </div>
                        <span class="admin-panel-count"><?= count($users) ?> tài khoản</span>
                    </div>

                    <?php if ($users === []): ?>
                        <p class="admin-empty">Chưa có tài khoản nào.</p>
                    <?php else: ?>
                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Tài khoản</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Số điện thoại</th>
                                        <th scope="col">Vai trò</th>
                                        <th scope="col">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
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
                                            <td><?= htmlspecialchars($user['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
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
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <p class="admin-note">Quản lý sản phẩm và đơn hàng sẽ được bổ sung khi các bảng dữ liệu tương ứng được thiết lập.</p>
            </section>
        </main>
    </div>
</body>

</html>
