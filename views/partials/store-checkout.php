<section class="shop-checkout-page" aria-labelledby="checkout-title">
    <div class="shop-cart-heading">
        <div>
            <span class="shop-section-kicker">THANH TOÁN</span>
            <h1 id="checkout-title">
                <?= $orderSuccessCode === null
                    ? 'Thông tin giao hàng'
                    : ($orderPaymentMethod === 'bank_transfer' ? 'Chờ thanh toán' : 'Đặt hàng thành công') ?>
            </h1>
        </div>
        <a class="shop-back-to-products" href="<?= BASE_URL ?>?action=cart">← Quay lại giỏ hàng</a>
    </div>

    <?php if ($orderSuccessCode !== null): ?>
        <div class="shop-order-success">
            <h2><?= $orderPaymentMethod === 'bank_transfer' ? 'Đơn hàng đang chờ thanh toán' : 'Cảm ơn bạn đã đặt hàng!' ?></h2>
            <p>Mã đơn hàng của bạn: <strong><?= htmlspecialchars($orderSuccessCode, ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if ($orderPaymentMethod === 'bank_transfer'): ?>
                <p>Quét mã QR bên dưới bằng ứng dụng ngân hàng để thanh toán. Đơn hàng chỉ được xác nhận sau khi cửa hàng kiểm tra nhận được tiền.</p>
                <?php if (BANK_TRANSFER_BANK_NAME !== '' && BANK_TRANSFER_ACCOUNT_NUMBER !== '' && BANK_TRANSFER_ACCOUNT_HOLDER !== ''): ?>
                    <div class="shop-transfer-details">
                        <h3>Thông tin chuyển khoản</h3>
                        <img
                            class="shop-payment-qr"
                            src="<?= BASE_URL ?>?action=payment_qr&amp;success=<?= urlencode($orderSuccessCode) ?>"
                            alt="Mã QR thanh toán cho đơn hàng <?= htmlspecialchars($orderSuccessCode, ENT_QUOTES, 'UTF-8') ?>">
                        <p>Ngân hàng: <strong><?= htmlspecialchars(BANK_TRANSFER_BANK_NAME, ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <p>Mã ngân hàng: <strong><?= htmlspecialchars(BANK_TRANSFER_BANK_BIN, ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <p>Số tài khoản: <strong><?= htmlspecialchars(BANK_TRANSFER_ACCOUNT_NUMBER, ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <p>Chủ tài khoản: <strong><?= htmlspecialchars(BANK_TRANSFER_ACCOUNT_HOLDER, ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <p>Số tiền: <strong><?= number_format($orderTotal, 0, ',', '.') ?> ₫</strong></p>
                        <p>Nội dung chuyển khoản: <strong><?= htmlspecialchars($orderSuccessCode, ENT_QUOTES, 'UTF-8') ?></strong></p>
                    </div>
                <?php else: ?>
                    <p class="shop-form-message is-error">
                        Cửa hàng chưa cấu hình tài khoản nhận tiền. Vui lòng liên hệ cửa hàng và cung cấp mã đơn hàng để nhận thông tin chuyển khoản.
                    </p>
                <?php endif; ?>
                <p class="shop-payment-pending">Trạng thái hiện tại: <strong>Chưa thanh toán</strong>. Trang này không xác nhận giao dịch tự động.</p>
            <?php else: ?>
                <p>Đơn hàng được thanh toán khi nhận hàng (COD). Cửa hàng sẽ liên hệ để xác nhận.</p>
            <?php endif; ?>
            <a class="shop-purchase-button" href="<?= BASE_URL ?>?action=order&amp;code=<?= urlencode($orderSuccessCode) ?>">Theo dõi đơn hàng</a>
            <a class="shop-order-continue-link" href="<?= BASE_URL ?>?action=products">Tiếp tục mua sắm</a>
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

                <fieldset class="shop-payment-methods">
                    <legend>Phương thức thanh toán</legend>
                    <label class="shop-payment-option">
                        <input type="radio" name="payment_method" value="cod" required <?= $paymentMethod === 'cod' ? 'checked' : '' ?>>
                        <span>
                            <strong>Thanh toán khi nhận hàng</strong>
                            <small>Thanh toán tiền mặt khi nhận đơn.</small>
                        </span>
                    </label>
                    <label class="shop-payment-option">
                        <input type="radio" name="payment_method" value="bank_transfer" required <?= $paymentMethod === 'bank_transfer' ? 'checked' : '' ?>>
                        <span>
                            <strong>Chuyển khoản ngân hàng</strong>
                            <small>Chuyển khoản ngân hàng; cửa hàng xác nhận thủ công.</small>
                        </span>
                    </label>
                    <p class="shop-payment-hint">
                        <?php if (BANK_TRANSFER_BANK_NAME !== '' && BANK_TRANSFER_ACCOUNT_NUMBER !== '' && BANK_TRANSFER_ACCOUNT_HOLDER !== ''): ?>
                            Thông tin tài khoản sẽ hiển thị sau khi bạn đặt hàng.
                        <?php else: ?>
                            Tài khoản nhận tiền chưa được cấu hình; cửa hàng sẽ cung cấp thông tin sau khi xác nhận đơn.
                        <?php endif; ?>
                    </p>
                </fieldset>

                <button class="shop-purchase-button" type="submit" <?= !$cartAvailable ? 'disabled' : '' ?>>
                    <?= $cartAvailable ? 'Đặt hàng' : 'Cập nhật tồn kho trước khi đặt' ?>
                </button>
            </form>

            <aside class="shop-cart-summary">
                <h2>Đơn hàng của bạn</h2>
                <?php foreach ($cartLines as $line): ?>
                    <div class="shop-checkout-line">
                        <div class="shop-checkout-product">
                            <div class="shop-checkout-product-image">
                                <?php if (!empty($line['product']['image_url'])): ?>
                                    <img
                                        src="<?= htmlspecialchars($line['product']['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($line['product']['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <span>Chưa có hình</span>
                                <?php endif; ?>
                            </div>
                            <span class="shop-checkout-product-name">
                                <strong><?= htmlspecialchars($line['product']['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ($line['size'] !== ''): ?>
                                    <small>Size <?= htmlspecialchars($line['size'], ENT_QUOTES, 'UTF-8') ?></small>
                                <?php endif; ?>
                                <small>Số lượng: <?= (int) $line['quantity'] ?></small>
                            </span>
                        </div>
                        <strong class="shop-checkout-product-total"><?= number_format((float) $line['line_total'], 0, ',', '.') ?> ₫</strong>
                    </div>
                <?php endforeach; ?>
                <p class="shop-cart-summary-total"><span>Tổng cộng</span><strong><?= number_format($cartTotal, 0, ',', '.') ?> ₫</strong></p>
            </aside>
        </div>
    <?php endif; ?>
</section>
