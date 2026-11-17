<?php
$resultMessages = [
    'created' => 'Đã thêm sản phẩm.',
    'updated' => 'Đã cập nhật sản phẩm.',
    'deleted' => 'Đã xóa sản phẩm.',
];
$resultInput = $_GET['result'] ?? '';
$resultMessage = is_string($resultInput) ? ($resultMessages[$resultInput] ?? null) : null;
?>

<section class="admin-product-section">
    <?php if ($resultMessage !== null): ?>
        <p class="admin-feedback admin-feedback-success" role="status"><?= htmlspecialchars($resultMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($productError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($productError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel" aria-labelledby="product-list-title">
        <div class="admin-panel-heading">
            <div>
                <h2 id="product-list-title">Danh sách sản phẩm</h2>
                <p>Sửa hoặc xóa sản phẩm trong cửa hàng</p>
            </div>
            <a class="admin-button" href="<?= BASE_URL ?>?action=admin&amp;section=products&amp;form=create">Thêm sản phẩm</a>
        </div>
        <?php if ($products === []): ?>
            <p class="admin-empty">Chưa có sản phẩm nào. Hãy thêm sản phẩm mới.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table admin-product-table">
                    <thead>
                        <tr>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Mã SKU</th>
                            <th scope="col">Danh mục</th>
                            <th scope="col">Giá</th>
                            <th scope="col">Số lượng</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <span class="admin-product-name">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($product['category_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= number_format((float) $product['price'], 0, ',', '.') ?> ₫</td>
                                <td><?= (int) $product['stock'] ?></td>
                                <td>
                                    <span class="admin-status <?= (int) $product['status'] === 1 ? 'admin-status-active' : 'admin-status-locked' ?>">
                                        <?= (int) $product['status'] === 1 ? 'Đang bán' : 'Đã ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-product-actions">
                                        <a class="admin-product-edit" href="<?= BASE_URL ?>?action=admin&amp;section=products&amp;product_id=<?= (int) $product['id'] ?>">Sửa</a>
                                        <form method="post" action="<?= BASE_URL ?>?action=admin&amp;section=products" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                            <button class="admin-product-delete" type="submit">Xóa</button>
                                        </form>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
