<?php
$orderStatusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
];
$paymentStatusLabels = [
    'unpaid' => 'Chưa thanh toán',
    'paid' => 'Đã thanh toán',
    'refunded' => 'Đã hoàn tiền',
];
$paymentMethodLabels = [
    'cod' => 'Nhận hàng',
    'bank_transfer' => 'Chuyển khoản',
];
?>

<section class="admin-product-section">
    <?php if ($orderError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel" aria-labelledby="order-list-title">
        <div class="admin-panel-heading">
            <div>
                <h2 id="order-list-title">Danh sách đơn hàng</h2>
                <p>Xem đơn hàng và cập nhật trạng thái xử lý</p>
            </div>
            <span class="admin-panel-count"><?= count($orders) ?> đơn hàng</span>
        </div>

        <form class="admin-order-filters" method="get" action="<?= BASE_URL ?>">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="section" value="orders">
            <label for="order-status-filter">Lọc trạng thái</label>
            <select id="order-status-filter" name="status">
                <option value="">Tất cả đơn hàng</option>
                <?php foreach ($orderStatuses as $status): ?>
                    <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" <?= $orderStatusFilter === $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars($orderStatusLabels[$status], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="admin-button" type="submit">Lọc</button>
            <?php if ($orderStatusFilter !== null): ?>
                <a class="admin-panel-link" href="<?= BASE_URL ?>?action=admin&amp;section=orders">Xóa bộ lọc</a>
            <?php endif; ?>
        </form>

        <?php if ($orders === []): ?>
            <p class="admin-empty">Chưa có đơn hàng<?= $orderStatusFilter !== null ? ' ở trạng thái này' : '' ?>.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table admin-order-table">
                    <thead>
                        <tr>
                            <th scope="col">Mã đơn</th>
                            <th scope="col">Khách hàng</th>
                            <th scope="col">Điện thoại</th>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Tổng tiền</th>
                            <th scope="col">Thanh toán</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col">Ngày đặt</th>
                            <th scope="col">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $orderRow): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($orderRow['order_code'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($orderRow['recipient_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($orderRow['recipient_phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) $orderRow['item_count'] ?></td>
                                <td><?= number_format((float) $orderRow['total_amount'], 0, ',', '.') ?> ₫</td>
                                <td>
                                    <?= htmlspecialchars($paymentMethodLabels[$orderRow['payment_method']] ?? $orderRow['payment_method'], ENT_QUOTES, 'UTF-8') ?>
                                    <small class="admin-order-payment-status"><?= htmlspecialchars($paymentStatusLabels[$orderRow['payment_status']] ?? $orderRow['payment_status'], ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td>
                                    <span class="admin-status <?= $orderRow['status'] === 'completed' ? 'admin-status-active' : ($orderRow['status'] === 'cancelled' ? 'admin-status-locked' : '') ?>">
                                        <?= htmlspecialchars($orderStatusLabels[$orderRow['status']] ?? $orderRow['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($orderRow['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a class="admin-product-edit" href="<?= BASE_URL ?>?action=admin&amp;section=orders&amp;order_id=<?= (int) $orderRow['id'] ?>">Xem / cập nhật</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
