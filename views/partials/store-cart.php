<section class="shop-cart-page" aria-labelledby="cart-title">
    <div class="shop-cart-heading">
        <div>
            <span class="shop-section-kicker">ĐƠN HÀNG CỦA BẠN</span>
            <h1 id="cart-title">Giỏ hàng</h1>
        </div>
        <a class="shop-back-to-products" href="<?= BASE_URL ?>?action=products">← Tiếp tục xem sản phẩm</a>
    </div>

    <?php if (!empty($cartError)): ?>
        <p class="shop-form-message is-error" role="alert"><?= htmlspecialchars($cartError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if (!empty($cartNotice)): ?>
        <p class="shop-form-message is-success" role="status"><?= htmlspecialchars($cartNotice, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($cartLines === []): ?>
        <div class="shop-cart-empty">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a class="shop-purchase-button" href="<?= BASE_URL ?>?action=products">Khám phá sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="shop-cart-layout">
            <div class="shop-cart-items">
                <?php foreach ($cartLines as $line): ?>
                    <?php $product = $line['product']; ?>
                    <article class="shop-cart-item">
                        <?php if ((int) $product['status'] === 1): ?>
                            <a class="shop-cart-item-image" href="<?= BASE_URL ?>?action=product&amp;id=<?= (int) $product['id'] ?>">
                        <?php else: ?>
                            <span class="shop-cart-item-image">
                        <?php endif; ?>
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <span>Chưa có hình ảnh</span>
                            <?php endif; ?>
                        <?php if ((int) $product['status'] === 1): ?></a><?php else: ?></span><?php endif; ?>
                        <div class="shop-cart-item-info">
                            <?php if ((int) $product['status'] === 1): ?>
                            <a href="<?= BASE_URL ?>?action=product&amp;id=<?= (int) $product['id'] ?>">
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <?php else: ?>
                                <strong><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?> (Ngừng bán)</strong>
                            <?php endif; ?>
                            <?php if ($line['size'] !== ''): ?>
                                <span>Size: <?= htmlspecialchars($line['size'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span><?= number_format((float) $product['price'], 0, ',', '.') ?> ₫ / sản phẩm</span>
                            <?php if (!$line['available']): ?>
                                <span class="shop-form-message is-error">Sản phẩm ngừng bán hoặc số lượng vượt quá tồn kho hiện tại.</span>
                            <?php endif; ?>
                        </div>
                        <form class="shop-cart-item-actions" method="post" action="<?= BASE_URL ?>?action=cart">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="size" value="<?= htmlspecialchars($line['size'], ENT_QUOTES, 'UTF-8') ?>">
                            <label class="visually-hidden" for="cart-quantity-<?= (int) $product['id'] ?>-<?= htmlspecialchars($line['size'] !== '' ? $line['size'] : 'na', ENT_QUOTES, 'UTF-8') ?>">Số lượng</label>
                            <input
                                id="cart-quantity-<?= (int) $product['id'] ?>-<?= htmlspecialchars($line['size'] !== '' ? $line['size'] : 'na', ENT_QUOTES, 'UTF-8') ?>"
                                name="quantity"
                                type="number"
                                min="1"
                                max="<?= max(1, (int) $product['stock']) ?>"
                                value="<?= (int) $line['quantity'] ?>"
                                required>
                            <button class="shop-cart-update" type="submit" name="operation" value="update">Cập nhật</button>
                            <button class="shop-cart-remove" type="submit" name="operation" value="remove">Xóa</button>
                        </form>
                        <strong class="shop-cart-line-total"><?= number_format((float) $line['line_total'], 0, ',', '.') ?> ₫</strong>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="shop-cart-summary">
                <h2>Tóm tắt đơn hàng</h2>
                <p><span>Tạm tính</span><strong><?= number_format($cartTotal, 0, ',', '.') ?> ₫</strong></p>
                <p><span>Phí giao hàng</span><span>Tính khi xác nhận</span></p>
                <p class="shop-cart-summary-total"><span>Tổng cộng</span><strong><?= number_format($cartTotal, 0, ',', '.') ?> ₫</strong></p>
                <?php if ($cartAvailable): ?>
                    <a class="shop-purchase-button" href="<?= BASE_URL ?>?action=checkout">Tiến hành đặt hàng</a>
                <?php else: ?>
                    <button class="shop-purchase-button" type="button" disabled>Cập nhật tồn kho trước khi đặt</button>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
</section>
