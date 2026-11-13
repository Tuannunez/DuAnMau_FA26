<div class="auth-form">
    <?php if (!empty($_GET['registered'])): ?>
        <div class="auth-alert auth-alert-success" role="status">Đăng ký thành công. Bạn có thể đăng nhập.</div>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <div class="auth-alert auth-alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>?action=login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="auth-field">
            <label for="email">Địa chỉ email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="ban@email.com" autocomplete="email" required>
        </div>

        <div class="auth-field">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
        </div>

        <button type="submit" class="auth-submit">Đăng nhập <span aria-hidden="true">→</span></button>
    </form>

    <p class="auth-switch">Chưa có tài khoản? <a href="<?= BASE_URL ?>?action=register">Tạo tài khoản</a></p>
</div>
