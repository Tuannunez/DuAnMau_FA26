<div class="auth-form">
    <?php if ($error !== null): ?>
        <div class="auth-alert auth-alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>?action=register">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="auth-field">
            <label for="full_name">Họ và tên</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nguyễn Văn An" maxlength="100" autocomplete="name" required>
        </div>

        <div class="auth-field">
            <label for="email">Địa chỉ email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="ban@email.com" maxlength="100" autocomplete="email" required>
        </div>

        <div class="auth-field">
            <label for="phone">Số điện thoại <span class="auth-optional">(không bắt buộc)</span></label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" placeholder="09xx xxx xxx" maxlength="15" autocomplete="tel">
        </div>

        <div class="auth-field">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" placeholder="Ít nhất 8 ký tự" minlength="8" autocomplete="new-password" required>
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu" minlength="8" autocomplete="new-password" required>
        </div>

        <button type="submit" class="auth-submit">Tạo tài khoản <span aria-hidden="true">→</span></button>
    </form>

    <p class="auth-switch">Đã có tài khoản? <a href="<?= BASE_URL ?>?action=login">Đăng nhập</a></p>
</div>
