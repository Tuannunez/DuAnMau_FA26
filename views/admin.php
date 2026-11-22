<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | TUAN LFC SHOP Admin</title>
    <link href="<?= BASE_URL ?>assets/admin.css?v=<?= filemtime(PATH_ROOT . 'assets/admin.css') ?>" rel="stylesheet">
</head>

<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="<?= BASE_URL ?>?action=admin">
                <span class="admin-brand-mark">TLC</span>
                <span>TUAN LFC <strong>SHOP</strong><small>SPORTS ADMIN</small></span>
            </a>

            <nav class="admin-navigation" aria-label="Điều hướng quản trị">
                <?php $currentGroup = null; ?>
                <?php foreach ($navigation as $key => $item): ?>
                    <?php if ($currentGroup !== $item['group']): ?>
                        <?php $currentGroup = $item['group']; ?>
                        <p class="admin-sidebar-label"><?= htmlspecialchars($currentGroup, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <a
                        class="admin-nav-link <?= $section === $key ? 'is-active' : '' ?>"
                        href="<?= BASE_URL ?>?action=admin&amp;section=<?= urlencode($key) ?>"
                        <?= $section === $key ? 'aria-current="page"' : '' ?>>
                        <span class="admin-nav-icon" aria-hidden="true"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

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
                    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
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

            <section class="admin-content">
                <?php if ($section === 'dashboard'): ?>
                    <div class="admin-welcome">
                        <div>
                            <p>Xin chào, <?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?></p>
                            <h2>Tổng quan cửa hàng</h2>
                            <span>Theo dõi doanh thu sản phẩm, tiền đã nhận và tình hình bán hàng.</span>
                        </div>
                        <span class="admin-welcome-mark" aria-hidden="true">LFC</span>
                    </div>

                    <div class="admin-stats admin-finance-stats">
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon admin-stat-icon-red" aria-hidden="true">₫</span>
                            <div>
                                <p>Tiền đã nhận</p>
                                <strong><?= number_format($revenue['received_amount'], 0, ',', '.') ?> ₫</strong>
                            </div>
                        </article>
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon admin-stat-icon-green" aria-hidden="true">◈</span>
                            <div>
                                <p>Doanh thu sản phẩm</p>
                                <strong><?= number_format($revenue['product_revenue'], 0, ',', '.') ?> ₫</strong>
                            </div>
                        </article>
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon admin-stat-icon-amber" aria-hidden="true">!</span>
                            <div>
                                <p>Đơn chưa thu tiền</p>
                                <strong><?= number_format($revenue['outstanding_amount'], 0, ',', '.') ?> ₫</strong>
                            </div>
                        </article>
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon admin-stat-icon-dark" aria-hidden="true">A</span>
                            <div>
                                <p>Đơn đã thanh toán</p>
                                <strong><?= number_format($revenue['paid_orders']) ?></strong>
                            </div>
                        </article>
                    </div>

                    <div class="admin-revenue-grid">
                        <section class="admin-panel admin-chart-panel" aria-labelledby="revenue-chart-title">
                            <div class="admin-panel-heading">
                                <div>
                                    <h2 id="revenue-chart-title">Tiền nhận về theo tháng</h2>
                                    <p>Doanh thu từ đơn đã thanh toán trong 6 tháng gần nhất</p>
                                </div>
                            </div>
                            <?php
                            $maxMonthlyRevenue = max(array_column($revenue['monthly_revenue'], 'revenue'));
                            ?>
                            <div class="admin-monthly-chart" role="img" aria-label="Biểu đồ tiền nhận về theo tháng">
                                <?php foreach ($revenue['monthly_revenue'] as $month): ?>
                                    <?php $barHeight = $maxMonthlyRevenue > 0 ? ($month['revenue'] / $maxMonthlyRevenue) * 100 : 0; ?>
                                    <div class="admin-monthly-column">
                                        <span class="admin-chart-value"><?= number_format($month['revenue'], 0, ',', '.') ?> ₫</span>
                                        <div class="admin-monthly-track">
                                            <span class="admin-monthly-bar" style="height: <?= $barHeight ?>%"></span>
                                        </div>
                                        <span class="admin-chart-label"><?= htmlspecialchars($month['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <section class="admin-panel admin-chart-panel" aria-labelledby="products-chart-title">
                            <div class="admin-panel-heading">
                                <div>
                                    <h2 id="products-chart-title">Doanh thu sản phẩm</h2>
                                    <p>Sản phẩm trong đơn đã thanh toán, trước giảm giá</p>
                                </div>
                            </div>
                            <?php $maxProductRevenue = $revenue['top_products'] === [] ? 0 : (float) $revenue['top_products'][0]['revenue']; ?>
                            <?php if ($revenue['top_products'] === []): ?>
                                <p class="admin-empty">Chưa có sản phẩm được thanh toán.</p>
                            <?php else: ?>
                                <div class="admin-product-chart">
                                    <?php foreach ($revenue['top_products'] as $product): ?>
                                        <?php $productBarWidth = $maxProductRevenue > 0 ? ((float) $product['revenue'] / $maxProductRevenue) * 100 : 0; ?>
                                        <div class="admin-product-chart-row">
                                            <div class="admin-product-chart-heading">
                                                <strong><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <span><?= number_format((float) $product['revenue'], 0, ',', '.') ?> ₫</span>
                                            </div>
                                            <div class="admin-product-track" aria-hidden="true">
                                                <span style="width: <?= $productBarWidth ?>%"></span>
                                            </div>
                                            <small>Đã bán <?= number_format((int) $product['quantity_sold']) ?> sản phẩm</small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>

                    <div class="admin-dashboard-grid">
                        <section class="admin-panel" aria-labelledby="latest-users-title">
                            <div class="admin-panel-heading">
                                <div>
                                    <h2 id="latest-users-title">Tài khoản mới nhất</h2>
                                    <p>Dữ liệu tài khoản hiện có trong hệ thống</p>
                                </div>
                                <a class="admin-panel-link" href="<?= BASE_URL ?>?action=admin&amp;section=accounts">Xem tất cả</a>
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
                                                <th scope="col">Vai trò</th>
                                                <th scope="col">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <?php require PATH_VIEW . 'partials/admin-user-row.php'; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </section>

                        <aside class="admin-panel admin-module-panel">
                            <div class="admin-panel-heading">
                                <div>
                                    <h2>Quản lý cửa hàng</h2>
                                    <p>Các khu vực quản trị</p>
                                </div>
                            </div>
                            <div class="admin-module-list">
                                <?php foreach (['orders', 'products', 'categories', 'promotions', 'reviews'] as $module): ?>
                                    <a href="<?= BASE_URL ?>?action=admin&amp;section=<?= urlencode($module) ?>">
                                        <span><?= htmlspecialchars($navigation[$module]['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?= htmlspecialchars($navigation[$module]['label'], ENT_QUOTES, 'UTF-8') ?>
                                        <small>
                                            <?= match ($module) {
                                                'products' => 'Quản lý sản phẩm',
                                                'categories' => 'Quản lý danh mục',
                                                'orders' => 'Theo dõi đơn hàng',
                                                'promotions' => 'Tạo và quản lý mã giảm',
                                                'reviews' => 'Duyệt đánh giá khách hàng',
                                                default => 'Chưa có dữ liệu',
                                            } ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </aside>
                    </div>
                <?php elseif (in_array($section, ['customers', 'accounts'], true)): ?>
                    <section class="admin-panel" aria-labelledby="user-list-title">
                        <div class="admin-panel-heading">
                            <div>
                                <h2 id="user-list-title"><?= $section === 'customers' ? 'Danh sách khách hàng' : 'Danh sách tài khoản' ?></h2>
                                <p><?= $section === 'customers' ? 'Các tài khoản có vai trò khách hàng' : 'Tài khoản và vai trò trong hệ thống' ?></p>
                            </div>
                            <span class="admin-panel-count"><?= count($users) ?> tài khoản</span>
                        </div>
                        <?php if ($users === []): ?>
                            <p class="admin-empty">Chưa có dữ liệu tài khoản.</p>
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
                                            <?php require PATH_VIEW . 'partials/admin-user-row.php'; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php elseif ($section === 'products'): ?>
                    <?php if ($showProductForm): ?>
                        <?php require PATH_VIEW . 'partials/admin-product-form.php'; ?>
                    <?php else: ?>
                        <?php require PATH_VIEW . 'partials/admin-products.php'; ?>
                    <?php endif; ?>
                <?php elseif ($section === 'categories'): ?>
                    <?php if ($showCategoryForm): ?>
                        <?php require PATH_VIEW . 'partials/admin-category-form.php'; ?>
                    <?php else: ?>
                        <?php require PATH_VIEW . 'partials/admin-categories.php'; ?>
                    <?php endif; ?>
                <?php elseif ($section === 'orders'): ?>
                    <?php if ($order !== null): ?>
                        <?php require PATH_VIEW . 'partials/admin-order-detail.php'; ?>
                    <?php else: ?>
                        <?php require PATH_VIEW . 'partials/admin-orders.php'; ?>
                    <?php endif; ?>
                <?php elseif ($section === 'promotions'): ?>
                    <?php require PATH_VIEW . 'partials/admin-promotions.php'; ?>
                <?php elseif ($section === 'reviews'): ?>
                    <?php require PATH_VIEW . 'partials/admin-reviews.php'; ?>
                <?php elseif ($section === 'settings'): ?>
                    <section class="admin-panel admin-settings-panel" aria-labelledby="settings-title">
                        <div class="admin-panel-heading">
                            <div>
                                <h2 id="settings-title">Thông tin tài khoản quản trị</h2>
                                <p>Thông tin hồ sơ đang dùng để đăng nhập quản trị</p>
                            </div>
                        </div>
                        <dl class="admin-settings-list">
                            <div><dt>Họ và tên</dt><dd><?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                            <div><dt>Email đăng nhập</dt><dd><?= htmlspecialchars($_SESSION['user']['email'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                            <div><dt>Vai trò</dt><dd><span class="admin-role admin-role-admin">Quản trị viên</span></dd></div>
                        </dl>
                        <p class="admin-note">Chỉnh sửa hồ sơ và đổi mật khẩu chưa được bật trong phiên bản này.</p>
                    </section>
                <?php else: ?>
                    <section class="admin-coming-soon">
                        <span class="admin-coming-icon" aria-hidden="true"><?= htmlspecialchars($navigation[$section]['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="admin-coming-label">KHU VỰC QUẢN TRỊ</span>
                        <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                        <p>Mục này đã có trong menu quản trị. Chức năng sẽ sẵn sàng khi có cấu trúc dữ liệu tương ứng.</p>
                        <div class="admin-coming-status"><span aria-hidden="true"></span> Chưa kết nối dữ liệu</div>
                        <a href="<?= BASE_URL ?>?action=admin">← Về Dashboard</a>
                    </section>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>

</html>
