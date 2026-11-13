<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'Home' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php $isAuthPage = isset($view) && in_array($view, ['login', 'register'], true); ?>
    <?php if ($isAuthPage): ?>
        <link href="<?= BASE_URL ?>assets/auth.css" rel="stylesheet">
    <?php endif; ?>
</head>

<body class="<?= $isAuthPage ? 'auth-body' : '' ?>">
    <?php if ($isAuthPage): ?>
        <main class="auth-page">
            <div class="auth-container">
                <a class="auth-brand" href="<?= BASE_URL ?>" aria-label="BaseExam - Trang chủ">
                    <span class="auth-brand-mark" aria-hidden="true">LS</span>
                    <span>LFC SHOP</span>
                </a>

                <section class="auth-card" aria-labelledby="auth-title">
                    <div class="auth-heading">
                        <span class="auth-kicker"><?= $view === 'login' ? 'CHÀO MỪNG TRỞ LẠI' : 'BẮT ĐẦU NGAY HÔM NAY' ?></span>
                        <h1 id="auth-title"><?= $title ?></h1>
                        <p><?= $view === 'login' ? 'Đăng nhập để tiếp tục hành trình sắm đồ của bạn.' : 'Tạo tài khoản để khám phá và mua sắm thả ga cùng LFC SHOP.' ?></p>
                    </div>

                    <?php require_once PATH_VIEW . $view . '.php'; ?>
                </section>

                <p class="auth-legal">Tiếp tục đồng nghĩa với việc bạn đồng ý sử dụng LFC SHOP một cách có trách nhiệm.</p>
            </div>
        </main>
    <?php else: ?>
        <nav class="navbar navbar-expand-xxl bg-light justify-content-center">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-uppercase" href="<?= BASE_URL ?>"><b>Trang chủ</b></a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            <?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?>
                            (<?= htmlspecialchars($_SESSION['user']['role'], ENT_QUOTES, 'UTF-8') ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <form method="post" action="<?= BASE_URL ?>?action=logout">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button class="nav-link text-uppercase border-0 bg-transparent" type="submit">Đăng xuất</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link text-uppercase" href="<?= BASE_URL ?>?action=login">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-uppercase" href="<?= BASE_URL ?>?action=register">Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="container">
            <h1 class="mt-3 mb-3"><?= $title ?? 'Home' ?></h1>

            <div class="row">
                <?php
                if (isset($view)) {
                    require_once PATH_VIEW . $view . '.php';
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

</body>

</html>