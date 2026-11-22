<section class="admin-product-section">
    <?php if ($promotionResult !== null): ?>
        <p class="admin-feedback admin-feedback-success" role="status"><?= htmlspecialchars($promotionResult, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($promotionError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($promotionError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <h2><?= $showPromotionForm ? ($editingPromotionId === null ? 'Thêm khuyến mãi' : 'Cập nhật khuyến mãi') : 'Danh sách khuyến mãi' ?></h2>
                <p><?= $showPromotionForm ? 'Thiết lập mã giảm giá và thời gian áp dụng' : 'Quản lý các chương trình khuyến mãi của cửa hàng' ?></p>
            </div>
            <?php if (!$showPromotionForm): ?>
                <a class="admin-button" href="<?= BASE_URL ?>?action=admin&amp;section=promotions&amp;form=create">Thêm khuyến mãi</a>
            <?php else: ?>
                <a class="admin-button admin-button-secondary" href="<?= BASE_URL ?>?action=admin&amp;section=promotions">Quay lại danh sách</a>
            <?php endif; ?>
        </div>

        <?php if ($showPromotionForm): ?>
            <form class="admin-product-form" method="post" action="<?= BASE_URL ?>?action=admin&amp;section=promotions">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="form_action" value="save">
                <input type="hidden" name="promotion_id" value="<?= $editingPromotionId !== null ? (int) $editingPromotionId : '' ?>">
                <label>Mã khuyến mãi
                    <input name="code" maxlength="30" required value="<?= htmlspecialchars($promotionForm['code'], ENT_QUOTES, 'UTF-8') ?>" placeholder="VD: SALE20">
                </label>
                <label>Tên chương trình
                    <input name="name" maxlength="180" required value="<?= htmlspecialchars($promotionForm['name'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Tên khuyến mãi">
                </label>
                <label>Loại giảm giá
                    <select name="discount_type">
                        <option value="percentage" <?= $promotionForm['discount_type'] === 'percentage' ? 'selected' : '' ?>>Giảm theo phần trăm (%)</option>
                        <option value="fixed" <?= $promotionForm['discount_type'] === 'fixed' ? 'selected' : '' ?>>Giảm số tiền cố định (₫)</option>
                    </select>
                </label>
                <label>Giá trị giảm
                    <input name="discount_value" type="number" min="0.01" step="0.01" required value="<?= htmlspecialchars($promotionForm['discount_value'], ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label>Bắt đầu
                    <input name="starts_at" type="datetime-local" value="<?= htmlspecialchars($promotionForm['starts_at'], ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label>Kết thúc
                    <input name="ends_at" type="datetime-local" value="<?= htmlspecialchars($promotionForm['ends_at'], ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="admin-product-form-wide">Mô tả
                    <textarea name="description" rows="3"><?= htmlspecialchars($promotionForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
                <label>Trạng thái
                    <select name="status">
                        <option value="1" <?= $promotionForm['status'] === '1' ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="0" <?= $promotionForm['status'] === '0' ? 'selected' : '' ?>>Tạm dừng</option>
                    </select>
                </label>
                <div class="admin-product-form-wide admin-product-form-actions">
                    <button class="admin-button" type="submit">Lưu khuyến mãi</button>
                    <a class="admin-button admin-button-secondary" href="<?= BASE_URL ?>?action=admin&amp;section=promotions">Hủy</a>
                </div>
            </form>
        <?php elseif ($promotions === []): ?>
            <p class="admin-empty">Chưa có khuyến mãi nào. Hãy tạo chương trình mới.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table admin-product-table">
                    <thead><tr><th>Mã</th><th>Tên chương trình</th><th>Giảm giá</th><th>Thời gian</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($promotions as $promotion): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($promotion['code'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($promotion['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $promotion['discount_type'] === 'percentage'
                                ? (float) $promotion['discount_value'] . '%'
                                : number_format((float) $promotion['discount_value'], 0, ',', '.') . ' ₫' ?></td>
                            <td>
                                <?= $promotion['starts_at'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($promotion['starts_at'])), ENT_QUOTES, 'UTF-8') : 'Không giới hạn' ?>
                                <br>
                                <?= $promotion['ends_at'] ? 'đến ' . htmlspecialchars(date('d/m/Y H:i', strtotime($promotion['ends_at'])), ENT_QUOTES, 'UTF-8') : 'Không hết hạn' ?>
                            </td>
                            <td><span class="admin-status <?= (int) $promotion['status'] === 1 ? 'admin-status-active' : 'admin-status-locked' ?>"><?= (int) $promotion['status'] === 1 ? 'Hoạt động' : 'Tạm dừng' ?></span></td>
                            <td>
                                <span class="admin-product-actions">
                                    <a class="admin-product-edit" href="<?= BASE_URL ?>?action=admin&amp;section=promotions&amp;promotion_id=<?= (int) $promotion['id'] ?>">Sửa</a>
                                    <form method="post" action="<?= BASE_URL ?>?action=admin&amp;section=promotions">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="form_action" value="toggle">
                                        <input type="hidden" name="promotion_id" value="<?= (int) $promotion['id'] ?>">
                                        <button class="admin-product-edit" type="submit"><?= (int) $promotion['status'] === 1 ? 'Tạm dừng' : 'Kích hoạt' ?></button>
                                    </form>
                                    <form method="post" action="<?= BASE_URL ?>?action=admin&amp;section=promotions" onsubmit="return confirm('Bạn có chắc muốn xóa khuyến mãi này?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="promotion_id" value="<?= (int) $promotion['id'] ?>">
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
