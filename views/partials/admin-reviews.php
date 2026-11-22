<section class="admin-product-section">
    <?php if ($reviewResult !== null): ?>
        <p class="admin-feedback admin-feedback-success" role="status"><?= htmlspecialchars($reviewResult, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($reviewError !== null): ?>
        <p class="admin-feedback admin-feedback-error" role="alert"><?= htmlspecialchars($reviewError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>Quản lý đánh giá</h2>
                <p>Duyệt, ẩn hoặc xóa đánh giá của khách hàng</p>
            </div>
            <span class="admin-panel-count"><?= count($reviews) ?> đánh giá</span>
        </div>
        <?php if ($reviews === []): ?>
            <p class="admin-empty">Chưa có đánh giá nào trong hệ thống.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table admin-product-table">
                    <thead><tr><th>Khách hàng</th><th>Sản phẩm</th><th>Điểm</th><th>Nội dung</th><th>Trạng thái</th><th>Ngày gửi</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($review['customer_name'] ?: 'Khách vãng lai', ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($review['product_name'] ?: 'Sản phẩm đã xóa', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="admin-review-stars"><?= str_repeat('★', (int) $review['rating']) ?><span><?= str_repeat('☆', 5 - (int) $review['rating']) ?></span></span></td>
                            <td class="admin-review-content"><?= htmlspecialchars($review['content'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php
                                $statusClass = $review['status'] === 'approved' ? 'admin-status-active' : ($review['status'] === 'hidden' ? 'admin-status-locked' : '');
                                $statusLabel = $review['status'] === 'approved' ? 'Đã duyệt' : ($review['status'] === 'hidden' ? 'Đã ẩn' : 'Chờ duyệt');
                                ?>
                                <span class="admin-status <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($review['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="admin-product-actions">
                                    <?php if ($review['status'] !== 'approved'): ?>
                                        <form method="post" action="<?= BASE_URL ?>?action=admin&amp;section=reviews">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="form_action" value="approve">
                                            <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                                            <button class="admin-product-edit" type="submit">Duyệt</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($review['status'] !== 'hidden'): ?>
                                        <form method="post" action="<?= BASE_URL ?>?action=admin&amp;section=reviews">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="form_action" value="hide">
                                            <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                                            <button class="admin-product-edit" type="submit">Ẩn</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?= BASE_URL ?>?action=admin&amp;section=reviews" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
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
