<?php
$productImages = array_values(array_filter([
    $product['image_url'],
    $product['additional_image'],
]));
?>

<section class="shop-product-detail" aria-labelledby="product-detail-title">
    <nav class="shop-breadcrumbs" aria-label="Đường dẫn">
        <a href="<?= BASE_URL ?>">Trang chủ</a>
        <span aria-hidden="true">/</span>
        <?php if (!empty($product['category_id']) && !empty($product['category_name'])): ?>
            <a href="<?= BASE_URL ?>?action=category&amp;id=<?= (int) $product['category_id'] ?>">
                <?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span aria-hidden="true">/</span>
        <?php endif; ?>
        <span><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></span>
    </nav>

    <div class="shop-product-detail-grid">
        <div class="shop-product-gallery" data-product-gallery>
            <div class="shop-product-detail-image">
                <?php if ($productImages !== []): ?>
                    <img
                        src="<?= htmlspecialchars($productImages[0], ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                        data-main-product-image>
                <?php else: ?>
                    <span>Chưa có hình ảnh</span>
                <?php endif; ?>
            </div>
            <?php if (count($productImages) > 1): ?>
                <div class="shop-product-thumbnails" aria-label="Ảnh sản phẩm">
                    <?php foreach ($productImages as $index => $image): ?>
                        <button
                            class="shop-product-thumbnail <?= $index === 0 ? 'is-active' : '' ?>"
                            type="button"
                            data-product-thumbnail
                            data-image-src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                            aria-label="Xem ảnh <?= $index + 1 ?>"
                            aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                            <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="shop-product-detail-info">
            <?php if (!empty($product['category_name'])): ?>
                <a
                    class="shop-product-category"
                    href="<?= BASE_URL ?>?action=category&amp;id=<?= (int) $product['category_id'] ?>">
                    <?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endif; ?>
            <h1 id="product-detail-title"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="shop-product-detail-sku">Mã sản phẩm: <?= htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="shop-product-detail-price"><?= number_format((float) $product['price'], 0, ',', '.') ?> ₫</p>
            <p class="shop-product-detail-stock <?= (int) $product['stock'] > 0 ? 'is-available' : 'is-unavailable' ?>">
                <?= (int) $product['stock'] > 0 ? 'Còn hàng · ' . (int) $product['stock'] . ' sản phẩm' : 'Tạm hết hàng' ?>
            </p>

            <?php if (!empty($cartError)): ?>
                <p class="shop-form-message is-error" role="alert"><?= htmlspecialchars($cartError, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($cartNotice)): ?>
                <p class="shop-form-message is-success" role="status"><?= htmlspecialchars($cartNotice, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <form class="shop-purchase-form" method="post" action="<?= BASE_URL ?>?action=cart">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="operation" value="add">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <?php if ($requiresSize): ?>
                    <label for="product-size">Size</label>
                    <select id="product-size" name="size" required>
                        <option value="">Chọn size</option>
                        <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size): ?>
                            <option value="<?= $size ?>"><?= $size ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <label for="product-quantity">Số lượng</label>
                <input
                    id="product-quantity"
                    name="quantity"
                    type="number"
                    min="1"
                    max="<?= max(1, (int) $product['stock']) ?>"
                    value="1"
                    required
                    <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                <button class="shop-purchase-button" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                    Thêm vào giỏ hàng
                </button>
            </form>

            <?php if (!empty($product['description'])): ?>
                <div class="shop-product-description">
                    <h2>Mô tả sản phẩm</h2>
                    <p><?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
            <?php else: ?>
                <div class="shop-product-description">
                    <h2>Mô tả sản phẩm</h2>
                    <p>Chưa có mô tả cho sản phẩm này.</p>
                </div>
            <?php endif; ?>

            <a class="shop-back-to-products" href="<?= BASE_URL ?>?action=products">← Tiếp tục xem sản phẩm</a>
        </div>
    </div>
</section>
