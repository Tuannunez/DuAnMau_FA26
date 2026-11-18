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
?>

<section class="admin-product-section">
    <?php if ($orderResult !== null): ?>
        <p class="admin-feedback admin-feedback-success" role="status"><?= htmlspecialchars($orderResult, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($orderError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel" aria-labelledby="order-detail-title">
        <div class="admin-panel-heading">
            <div>
                <h2 id="order-detail-title">Đơn hàng <?= htmlspecialchars($order['order_code'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p>Đặt lúc <?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a class="admin-panel-link" href="<?= BASE_URL ?>?action=admin&amp;section=orders">← Quay lại danh sách</a>
        </div>

        <div class="admin-order-detail-grid">
            <section class="admin-order-block" aria-labelledby="order-customer-title">
                <h3 id="order-customer-title">Thông tin nhận hàng</h3>
                <dl class="admin-order-info">
                    <div><dt>Người nhận</dt><dd><?= htmlspecialchars($order['recipient_name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Email</dt><dd><?= htmlspecialchars($order['recipient_email'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Số điện thoại</dt><dd><?= htmlspecialchars($order['recipient_phone'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Địa chỉ</dt><dd><?= htmlspecialchars($order['shipping_address'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <?php if (!empty($order['note'])): ?>
                        <div><dt>Ghi chú</dt><dd><?= nl2br(htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8')) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </section>

            <section class="admin-order-block" aria-labelledby="order-summary-title">
                <h3 id="order-summary-title">Tổng kết</h3>
                <dl class="admin-order-info">
                    <div><dt>Tạm tính</dt><dd><?= number_format((float) $order['subtotal'], 0, ',', '.') ?> ₫</dd></div>
                    <div><dt>Giảm giá</dt><dd><?= number_format((float) $order['discount_amount'], 0, ',', '.') ?> ₫</dd></div>
                    <div><dt>Tổng thanh toán</dt><dd><strong><?= number_format((float) $order['total_amount'], 0, ',', '.') ?> ₫</strong></dd></div>
                    <div><dt>Phương thức</dt><dd><?= htmlspecialchars($order['payment_method'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Tài khoản</dt><dd><?= htmlspecialchars($order['account_name'] ?? 'Khách vãng lai', ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
            </section>
        </div>

        <div class="admin-order-items">
            <h3>Sản phẩm trong đơn</h3>
            <?php if ($orderItems === []): ?>
                <p class="admin-empty">Đơn hàng chưa có dòng sản phẩm.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Sản phẩm</th>
                                <th scope="col">Mã SKU</th>
                                <th scope="col">Size</th>
                                <th scope="col">Đơn giá</th>
                                <th scope="col">Số lượng</th>
                                <th scope="col">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($item['sku'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($item['size'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= number_format((float) $item['unit_price'], 0, ',', '.') ?> ₫</td>
                                    <td><?= (int) $item['quantity'] ?></td>
                                    <td><?= number_format((float) $item['unit_price'] * (int) $item['quantity'], 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <form class="admin-order-status-form" method="post" action="<?= BASE_URL ?>?action=admin&amp;section=orders">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="form_action" value="update_status">
            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
            <label>
                Trạng thái đơn hàng
                <select name="status" required>
                    <?php foreach ($orderStatuses as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                            <?= htmlspecialchars($orderStatusLabels[$status], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Trạng thái thanh toán
                <select name="payment_status" required>
                    <?php foreach ($paymentStatuses as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" <?= $order['payment_status'] === $status ? 'selected' : '' ?>>
                            <?= htmlspecialchars($paymentStatusLabels[$status], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="admin-button" type="submit">Lưu trạng thái</button>
        </form>
    </section>
</section>
