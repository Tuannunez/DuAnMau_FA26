<section class="shop-orders-page" aria-labelledby="customer-order-title">
    <?php if (!empty($reviewNotice)): ?>
        <p class="shop-form-message is-success" role="status"><?= htmlspecialchars($reviewNotice, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if (!empty($reviewError)): ?>
        <p class="shop-form-message is-error" role="alert"><?= htmlspecialchars($reviewError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <div class="shop-orders-heading">
        <div>
            <span class="shop-section-kicker">CHI TIẾT ĐƠN HÀNG</span>
            <h1 id="customer-order-title"><?= htmlspecialchars($order['order_code'], ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <div class="shop-order-detail-actions">
            <a class="shop-back-to-products" href="<?= BASE_URL ?>?action=orders">← Danh sách đơn hàng</a>
            <?php if ($canDeleteOrder): ?>
                <form method="post" action="<?= BASE_URL ?>?action=order_delete"
                      onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="order_code" value="<?= htmlspecialchars($order['order_code'], ENT_QUOTES, 'UTF-8') ?>">
                    <button class="shop-order-delete-button" type="submit">Xóa đơn hàng</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="shop-order-tracking">
        <div class="shop-order-tracking-status">
            <div>
                <span>Trạng thái đơn</span>
                <strong class="shop-order-status status-<?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($orderStatusLabels[$order['status']] ?? $order['status'], ENT_QUOTES, 'UTF-8') ?>
                </strong>
            </div>
            <div>
                <span>Thanh toán</span>
                <strong><?= htmlspecialchars($paymentStatusLabels[$order['payment_status']] ?? $order['payment_status'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div>
                <span>Phương thức</span>
                <strong><?= htmlspecialchars($paymentMethodLabels[$order['payment_method']] ?? $order['payment_method'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div>
                <span>Ngày đặt</span>
                <strong><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>

        <?php if ($order['payment_method'] === 'bank_transfer' && $order['payment_status'] === 'unpaid'): ?>
            <div class="shop-order-payment-notice">
                <p>Đơn hàng đang chờ thanh toán. Quét mã QR bên dưới để chuyển khoản; cửa hàng sẽ cập nhật sau khi kiểm tra giao dịch.</p>
                <?php if (BANK_TRANSFER_BANK_BIN !== '' && BANK_TRANSFER_ACCOUNT_NUMBER !== ''): ?>
                    <img
                        class="shop-payment-qr"
                        src="<?= BASE_URL ?>?action=payment_qr&amp;success=<?= urlencode($order['order_code']) ?>"
                        alt="Mã QR thanh toán đơn <?= htmlspecialchars($order['order_code'], ENT_QUOTES, 'UTF-8') ?>">
                    <p>Số tiền: <strong><?= number_format((float) $order['total_amount'], 0, ',', '.') ?> ₫</strong></p>
                    <p>Nội dung chuyển khoản: <strong><?= htmlspecialchars($order['order_code'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="shop-customer-order-items" aria-labelledby="customer-order-items-title">
            <h2 id="customer-order-items-title">Sản phẩm đã đặt</h2>
            <?php foreach ($orderItems as $item): ?>
                <div class="shop-customer-order-item">
                    <div>
                        <strong><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($item['size'])): ?>
                            <span>Size: <?= htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <span>SKU: <?= htmlspecialchars($item['sku'] ?? '—', ENT_QUOTES, 'UTF-8') ?> · Số lượng: <?= (int) $item['quantity'] ?></span>
                        <?php if ($order['status'] === 'completed' && $canReviewOrder): ?>
                            <?php if (!empty($reviewStates[(int) $item['product_id']])): ?>
                                <span class="shop-review-complete">Bạn đã gửi đánh giá cho sản phẩm này.</span>
                            <?php else: ?>
                                <form class="shop-review-form" method="post" action="<?= BASE_URL ?>?action=review_create">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="order_code" value="<?= htmlspecialchars($order['order_code'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                    <label>Số sao
                                        <select name="rating" required>
                                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                                <option value="<?= $rating ?>"><?= $rating ?> sao</option>
                                            <?php endfor; ?>
                                        </select>
                                    </label>
                                    <label>Nội dung đánh giá
                                        <textarea name="content" rows="2" maxlength="1000" placeholder="Chia sẻ trải nghiệm của bạn"></textarea>
                                    </label>
                                    <button type="submit">Gửi đánh giá</button>
                                </form>
                            <?php endif; ?>
                        <?php elseif ($order['status'] !== 'completed'): ?>
                            <span class="shop-review-locked">Chỉ đánh giá sau khi đơn hàng giao thành công.</span>
                        <?php endif; ?>
                    </div>
                    <strong><?= number_format((float) $item['unit_price'] * (int) $item['quantity'], 0, ',', '.') ?> ₫</strong>
                </div>
            <?php endforeach; ?>
        </section>

        <div class="shop-order-tracking-bottom">
            <section>
                <h2>Thông tin giao hàng</h2>
                <p><?= htmlspecialchars($order['recipient_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars($order['recipient_phone'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars($order['recipient_email'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= nl2br(htmlspecialchars($order['shipping_address'], ENT_QUOTES, 'UTF-8')) ?></p>
            </section>
            <section class="shop-order-total">
                <h2>Tổng thanh toán</h2>
                <p>Tạm tính <strong><?= number_format((float) $order['subtotal'], 0, ',', '.') ?> ₫</strong></p>
                <p>Giảm giá <strong><?= number_format((float) $order['discount_amount'], 0, ',', '.') ?> ₫</strong></p>
                <p class="shop-order-grand-total">Tổng cộng <strong><?= number_format((float) $order['total_amount'], 0, ',', '.') ?> ₫</strong></p>
            </section>
        </div>
    </div>
</section>
