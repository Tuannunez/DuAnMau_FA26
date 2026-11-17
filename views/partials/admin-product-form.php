<?php $isEditingProduct = $editingProductId !== null; ?>

<section class="admin-product-section">
    <?php if ($productError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($productError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel admin-product-form-panel" aria-labelledby="product-form-title">
        <div class="admin-panel-heading">
            <div>
                <h2 id="product-form-title"><?= $isEditingProduct ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm' ?></h2>
                <p>Nhập thông tin sản phẩm cần quản lý</p>
            </div>
            <a class="admin-panel-link" href="<?= BASE_URL ?>?action=admin&amp;section=products">← Quay lại danh sách</a>
        </div>

        <form class="admin-product-form" method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>?action=admin&amp;section=products">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="form_action" value="save">
            <input type="hidden" name="product_id" value="<?= $isEditingProduct ? (int) $editingProductId : '' ?>">

            <label>
                Tên sản phẩm
                <input name="name" type="text" maxlength="180" required value="<?= htmlspecialchars($productForm['name'], ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Mã sản phẩm (SKU)
                <input name="sku" type="text" maxlength="80" required value="<?= htmlspecialchars($productForm['sku'], ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Danh mục
                <select name="category_id">
                    <option value="">-- Chưa phân loại --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (string) $productForm['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Giá bán
                <input name="price" type="number" min="0" step="0.01" required value="<?= htmlspecialchars((string) $productForm['price'], ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Số lượng
                <input name="stock" type="number" min="0" step="1" required value="<?= htmlspecialchars((string) $productForm['stock'], ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Trạng thái
                <select name="status">
                    <option value="1" <?= (string) $productForm['status'] === '1' ? 'selected' : '' ?>>Đang bán</option>
                    <option value="0" <?= (string) $productForm['status'] === '0' ? 'selected' : '' ?>>Ẩn</option>
                </select>
            </label>
            <label>
                Ảnh sản phẩm 1
                <input name="image_1" type="file" accept="image/jpeg,image/png,image/webp">
                <small>Không bắt buộc. JPG, PNG hoặc WebP; tối đa 5 MB.</small>
                <?php if (!empty($productImages[0])): ?>
                    <img class="admin-product-image-preview" src="<?= htmlspecialchars($productImages[0], ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh sản phẩm hiện tại 1">
                <?php endif; ?>
            </label>
            <label>
                Ảnh sản phẩm 2
                <input name="image_2" type="file" accept="image/jpeg,image/png,image/webp">
                <small>Không bắt buộc. Bỏ trống để giữ ảnh hiện tại; tối đa 5 MB.</small>
                <?php if (!empty($productImages[1])): ?>
                    <img class="admin-product-image-preview" src="<?= htmlspecialchars($productImages[1], ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh sản phẩm hiện tại 2">
                <?php endif; ?>
            </label>
            <label class="admin-product-form-wide">
                Mô tả
                <textarea name="description" rows="4"><?= htmlspecialchars((string) $productForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <div class="admin-product-form-actions admin-product-form-wide">
                <button class="admin-button" type="submit"><?= $isEditingProduct ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></button>
                <a class="admin-button admin-button-secondary" href="<?= BASE_URL ?>?action=admin&amp;section=products">Hủy</a>
            </div>
        </form>
    </section>
</section>
