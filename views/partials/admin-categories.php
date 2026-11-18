<?php
$resultMessages = [
    'created' => 'Đã thêm danh mục.',
    'updated' => 'Đã cập nhật danh mục.',
    'deleted' => 'Đã xóa danh mục.',
];
$resultInput = $_GET['result'] ?? '';
$resultMessage = is_string($resultInput) ? ($resultMessages[$resultInput] ?? null) : null;
?>

<section class="admin-product-section">
    <?php if ($resultMessage !== null): ?>
        <p class="admin-feedback admin-feedback-success" role="status"><?= htmlspecialchars($resultMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($categoryError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($categoryError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel" aria-labelledby="category-list-title">
        <div class="admin-panel-heading">
            <div>
                <h2 id="category-list-title">Danh sách danh mục</h2>
                <p>Quản lý các nhóm sản phẩm trong cửa hàng</p>
            </div>
            <a class="admin-button" href="<?= BASE_URL ?>?action=admin&amp;section=categories&amp;form=create">Thêm danh mục</a>
        </div>
        <?php if ($categoryRows === []): ?>
            <p class="admin-empty">Chưa có danh mục nào. Hãy thêm danh mục mới.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table admin-category-table">
                    <thead>
                        <tr>
                            <th scope="col">Tên danh mục</th>
                            <th scope="col">Danh mục cha</th>
                            <th scope="col">Slug</th>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categoryRows as $category): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($category['parent_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) $category['product_count'] ?></td>
                                <td>
                                    <span class="admin-status <?= (int) $category['status'] === 1 ? 'admin-status-active' : 'admin-status-locked' ?>">
                                        <?= (int) $category['status'] === 1 ? 'Hiển thị' : 'Đã ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-product-actions">
                                        <a
                                            class="admin-product-edit"
                                            href="<?= BASE_URL ?>?action=admin&amp;section=categories&amp;category_id=<?= (int) $category['id'] ?>">
                                            Sửa
                                        </a>
                                        <form
                                            method="post"
                                            action="<?= BASE_URL ?>?action=admin&amp;section=categories"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
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
