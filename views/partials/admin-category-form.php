<?php $isEditingCategory = $editingCategoryId !== null; ?>

<section class="admin-product-section">
    <?php if ($categoryError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($categoryError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel" aria-labelledby="category-form-title">
        <div class="admin-panel-heading">
            <div>
                <h2 id="category-form-title"><?= $isEditingCategory ? 'Chỉnh sửa danh mục' : 'Thêm danh mục' ?></h2>
                <p>Đường dẫn danh mục được tự tạo từ tên</p>
            </div>
            <a class="admin-panel-link" href="<?= BASE_URL ?>?action=admin&amp;section=categories">← Quay lại danh sách</a>
        </div>

        <form class="admin-product-form" method="post" action="<?= BASE_URL ?>?action=admin&amp;section=categories">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="form_action" value="save">
            <input type="hidden" name="category_id" value="<?= $isEditingCategory ? (int) $editingCategoryId : '' ?>">

            <label>
                Tên danh mục
                <input
                    name="name"
                    type="text"
                    maxlength="150"
                    required
                    value="<?= htmlspecialchars($categoryForm['name'], ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Danh mục cha
                <select name="parent_id">
                    <option value="">-- Không có --</option>
                    <?php foreach ($parentCategories as $parentCategory): ?>
                        <option
                            value="<?= (int) $parentCategory['id'] ?>"
                            <?= (string) $categoryForm['parent_id'] === (string) $parentCategory['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($parentCategory['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="admin-product-form-wide">
                Mô tả
                <textarea name="description" rows="4"><?= htmlspecialchars($categoryForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <label>
                Trạng thái
                <select name="status">
                    <option value="1" <?= $categoryForm['status'] === '1' ? 'selected' : '' ?>>Hiển thị</option>
                    <option value="0" <?= $categoryForm['status'] === '0' ? 'selected' : '' ?>>Ẩn</option>
                </select>
            </label>
            <div class="admin-product-form-actions admin-product-form-wide">
                <button class="admin-button" type="submit"><?= $isEditingCategory ? 'Lưu thay đổi' : 'Thêm danh mục' ?></button>
                <a class="admin-button admin-button-secondary" href="<?= BASE_URL ?>?action=admin&amp;section=categories">Hủy</a>
            </div>
        </form>
    </section>
</section>
