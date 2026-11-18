<section class="shop-checkout-page" aria-labelledby="checkout-title">
    <div class="shop-cart-heading">
        <div>
            <span class="shop-section-kicker">THANH TOÁN</span>
            <h1 id="checkout-title"><?= $orderSuccessCode === null ? 'Thông tin giao hàng' : 'Đặt hàng thành công' ?></h1>
        </div>
        <a class="shop-back-to-products" href="<?= BASE_URL ?>?action=cart">← Quay lại giỏ hàng</a>
    </div>

    <?php if ($orderSuccessCode !== null): ?>
        <div class="shop-order-success" role="status">
            <h2>Cảm ơn bạn đã đặt hàng!</h2>
            <p>Mã đơn hàng của bạn: <strong><?= htmlspecialchars($orderSuccessCode, ENT_QUOTES, 'UTF-8') ?></strong></p>
            <p>Đơn hàng được thanh toán khi nhận hàng (COD). Cửa hàng sẽ liên hệ để xác nhận.</p>
            <a class="shop-purchase-button" href="<?= BASE_URL ?>?action=products">Tiếp tục mua sắm</a>
        </div>
    <?php elseif ($cartLines === []): ?>
        <div class="shop-cart-empty">
            <?php if (!empty($checkoutError)): ?>
                <p class="shop-form-message is-error" role="alert"><?= htmlspecialchars($checkoutError, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <p>Giỏ hàng đang trống. Hãy chọn sản phẩm trước khi đặt hàng.</p>
            <a class="shop-purchase-button" href="<?= BASE_URL ?>?action=products">Xem sản phẩm</a>
        </div>
    <?php else: ?>
        <?php if (!empty($checkoutError)): ?>
            <p class="shop-form-message is-error" role="alert"><?= htmlspecialchars($checkoutError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <div class="shop-checkout-layout">
            <form class="shop-checkout-form" method="post" action="<?= BASE_URL ?>?action=checkout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label for="checkout-name">Tên người nhận</label>
                <input id="checkout-name" name="name" type="text" maxlength="150" value="<?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?>" required>

                <label for="checkout-email">Email</label>
                <input id="checkout-email" name="email" type="email" maxlength="100" value="<?= htmlspecialchars($customer['email'], ENT_QUOTES, 'UTF-8') ?>" required>

                <label for="checkout-phone">Số điện thoại</label>
                <input id="checkout-phone" name="phone" type="tel" maxlength="20" value="<?= htmlspecialchars($customer['phone'], ENT_QUOTES, 'UTF-8') ?>" required>

                <label for="checkout-address">Địa chỉ giao hàng</label>
                <textarea id="checkout-address" name="address" maxlength="500" rows="3" required><?= htmlspecialchars($customer['address'], ENT_QUOTES, 'UTF-8') ?></textarea>

                <label for="checkout-note">Ghi chú (không bắt buộc)</label>
                <textarea id="checkout-note" name="note" rows="3"><?= htmlspecialchars($customer['note'], ENT_QUOTES, 'UTF-8') ?></textarea>

                <button class="shop-purchase-button" type="submit" <?= !$cartAvailable ? 'disabled' : '' ?>>
                    <?= $cartAvailable ? 'Đặt hàng (thanh toán COD)' : 'Cập nhật tồn kho trước khi đặt' ?>
                </button>
            </form>

            <aside class="shop-cart-summary">
                <h2>Đơn hàng của bạn</h2>
                <?php foreach ($cartLines as $line): ?>
                    <p class="shop-checkout-line">
                        <span>
                            <?= htmlspecialchars($line['product']['name'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($line['size'] !== ''): ?>
                                · Size <?= htmlspecialchars($line['size'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                            × <?= (int) $line['quantity'] ?>
                        </span>
                        <strong><?= number_format((float) $line['line_total'], 0, ',', '.') ?> ₫</strong>
                    </p>
                <?php endforeach; ?>
                <p class="shop-cart-summary-total"><span>Tổng cộng</span><strong><?= number_format($cartTotal, 0, ',', '.') ?> ₫</strong></p>
            </aside>
        </div>
    <?php endif; ?>
</section>
