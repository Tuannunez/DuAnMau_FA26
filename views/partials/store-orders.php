<section class="shop-orders-page" aria-labelledby="orders-title">
    <div class="shop-orders-heading">
        <div>
            <span class="shop-section-kicker">THEO DÕI ĐƠN ĐÃ ĐẶT</span>
            <h1 id="orders-title">Đơn hàng của tôi</h1>
        </div>
        <a class="shop-back-to-products" href="<?= BASE_URL ?>?action=products">← Tiếp tục mua sắm</a>
    </div>

    <?php if (!empty($orderNotice)): ?>
        <p class="shop-form-message <?= $orderNoticeSuccess ? 'is-success' : 'is-error' ?>" role="status">
            <?= htmlspecialchars($orderNotice, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($orders === []): ?>
        <div class="shop-orders-empty">
            <p>Bạn chưa có đơn hàng nào trong tài khoản này hoặc phiên mua sắm hiện tại.</p>
            <?php if (!isset($_SESSION['user'])): ?>
                <p>Đơn khách chưa đăng nhập được lưu theo phiên trình duyệt. Hãy dùng đúng trình duyệt đã đặt hàng để xem lại.</p>
            <?php endif; ?>
            <a class="shop-purchase-button" href="<?= BASE_URL ?>?action=products">Khám phá sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="shop-orders-list">
            <?php foreach ($orders as $orderRow): ?>
                <article class="shop-order-card">
                    <div class="shop-order-card-heading">
                        <div>
                            <span>Mã đơn</span>
                            <strong><?= htmlspecialchars($orderRow['order_code'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <time datetime="<?= htmlspecialchars($orderRow['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(date('d/m/Y H:i', strtotime($orderRow['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                        </time>
                    </div>
                    <div class="shop-order-card-info">
                        <span><?= (int) $orderRow['item_count'] ?> sản phẩm</span>
                        <span><?= htmlspecialchars($paymentMethodLabels[$orderRow['payment_method']] ?? $orderRow['payment_method'], ENT_QUOTES, 'UTF-8') ?></span>
                        <strong><?= number_format((float) $orderRow['total_amount'], 0, ',', '.') ?> ₫</strong>
                    </div>
                    <div class="shop-order-card-status">
                        <span class="shop-order-status status-<?= htmlspecialchars($orderRow['status'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($orderStatusLabels[$orderRow['status']] ?? $orderRow['status'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="shop-payment-status">
                            <?= htmlspecialchars($paymentStatusLabels[$orderRow['payment_status']] ?? $orderRow['payment_status'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <a href="<?= BASE_URL ?>?action=order&amp;code=<?= urlencode($orderRow['order_code']) ?>">Xem chi tiết</a>
                        <?php if (in_array($orderRow['status'], ['completed', 'cancelled'], true)): ?>
                            <form method="post" action="<?= BASE_URL ?>?action=order_delete"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_code" value="<?= htmlspecialchars($orderRow['order_code'], ENT_QUOTES, 'UTF-8') ?>">
                                <button class="shop-order-delete-button" type="submit">Xóa đơn</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
