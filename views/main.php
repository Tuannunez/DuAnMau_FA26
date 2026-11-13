<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'LFC SHOP', ENT_QUOTES, 'UTF-8') ?> | LFC SHOP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php $isAuthPage = isset($view) && in_array($view, ['login', 'register'], true); ?>
    <?php $isHomePage = !isset($view); ?>
    <?php if ($isAuthPage): ?>
        <link href="<?= BASE_URL ?>assets/auth.css" rel="stylesheet">
    <?php elseif ($isHomePage): ?>
        <link href="<?= BASE_URL ?>assets/home.css" rel="stylesheet">
        <script src="<?= BASE_URL ?>assets/home.js" defer></script>
    <?php endif; ?>
</head>

<body class="<?= $isAuthPage ? 'auth-body' : '' ?>">
    <?php if ($isAuthPage): ?>
        <main class="auth-page">
            <div class="auth-container">
                <a class="auth-brand" href="<?= BASE_URL ?>" aria-label="BaseExam - Trang chủ">
                    <span class="auth-brand-mark" aria-hidden="true">LS</span>
                    <span> TUAN LFC SHOP</span>
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
    <?php elseif ($isHomePage): ?>
        <header class="shop-header">
            <div class="shop-header-inner">
                <a class="shop-logo" href="<?= BASE_URL ?>" aria-label="LFC SHOP - Trang chủ">
                    <span class="shop-logo-mark" aria-hidden="true">TLC</span>
                    <span class="shop-logo-name"> TUAN LFC <strong>SHOP</strong><small>THE REDS, ALWAYS</small></span>
                </a>

                <nav class="shop-nav" aria-label="Điều hướng chính">
                    <a class="shop-nav-link is-active" href="#shop-products">Sản phẩm</a>
                    <a class="shop-nav-link" href="#football-shirts">Áo bóng đá</a>
                    <a class="shop-nav-link" href="#football-boots">Giày bóng đá</a>
                    <a class="shop-nav-link" href="#souvenirs">Đồ lưu niệm</a>
                </nav>

                <form class="shop-search" role="search" data-category-search>
                    <label class="visually-hidden" for="shop-search-input">Tìm kiếm sản phẩm</label>
                    <input id="shop-search-input" type="search" placeholder="Tìm kiếm..." autocomplete="off">
                    <button type="submit" aria-label="Tìm kiếm">
                        <span aria-hidden="true">⌕</span>
                    </button>
                </form>

                <div class="shop-account-actions">
                    <span class="shop-order-placeholder" aria-disabled="true">Đơn hàng <small>Sắp có</small></span>
                    <span class="shop-account-divider" aria-hidden="true"></span>
                    <?php if (isset($_SESSION['user'])): ?>
                        <span class="shop-account-name">
                            <?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <form method="post" action="<?= BASE_URL ?>?action=logout">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button class="shop-account-link" type="submit">Đăng xuất</button>
                        </form>
                    <?php else: ?>
                        <a class="shop-account-link" href="<?= BASE_URL ?>?action=login">Đăng nhập</a>
                        <a class="shop-account-register" href="<?= BASE_URL ?>?action=register">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="shop-main">
            <section class="shop-hero" id="banner" aria-label="Bộ sưu tập LFC Shop">
                <img
                    class="shop-hero-image"
                    src="<?= BASE_URL ?>assets/images/banner1.png"
                    alt="LFC Shop - You'll Never Walk Alone, hàng chính hãng, ưu đãi hấp dẫn, giao hàng nhanh"
                    fetchpriority="high">
            </section>

            <section class="shop-products" id="shop-products" aria-labelledby="shop-products-title">
                <div class="shop-section-heading">
                    <div>
                        <span class="shop-section-kicker">DÀNH CHO CÁC REDS</span>
                        <h1 id="shop-products-title">Khám phá sản phẩm</h1>
                    </div>
                    <p class="shop-search-status" data-search-status aria-live="polite"></p>
                </div>

                <div class="shop-category-grid">
                    <article class="shop-category-card" id="football-shirts" data-search-item data-search-terms="áo bóng đá áo Liverpool jersey áo đấu">
                        <span class="shop-category-number">01 / COLLECTION</span>
                        <h2>Áo bóng đá</h2>
                        <p>Bộ sưu tập áo dành cho những trái tim luôn hướng về Anfield.</p>
                        <span class="shop-category-note">Danh mục sắp ra mắt</span>
                    </article>
                    <article class="shop-category-card shop-category-card-dark" id="football-boots" data-search-item data-search-terms="giày bóng đá giày đá banh football boots">
                        <span class="shop-category-number">02 / COLLECTION</span>
                        <h2>Giày bóng đá</h2>
                        <p>Sẵn sàng cho trận đấu tiếp theo với lựa chọn phù hợp phong cách của bạn.</p>
                        <span class="shop-category-note">Danh mục sắp ra mắt</span>
                    </article>
                    <article class="shop-category-card shop-category-card-souvenir" id="souvenirs" data-search-item data-search-terms="đồ lưu niệm quà tặng phụ kiện khăn cốc móc khóa Liverpool">
                        <span class="shop-category-number">03 / COLLECTION</span>
                        <h2>Đồ lưu niệm</h2>
                        <p>Phụ kiện và quà tặng mang sắc đỏ dành cho các cổ động viên Liverpool.</p>
                        <span class="shop-category-note">Danh mục sắp ra mắt</span>
                    </article>
                    <p class="shop-search-empty" data-search-empty hidden>Không tìm thấy danh mục phù hợp.</p>
                </div>
            </section>

            <section class="shop-highlights" aria-label="Cam kết của LFC Shop">
                <article class="shop-highlight">
                    <span class="shop-highlight-icon" aria-hidden="true">01</span>
                    <div>
                        <h2>Chuẩn chất The Reds </h2>
                        <p>Mang sắc đỏ Liverpool đến gần bạn hơn</p>
                    </div>
                </article>
                <article class="shop-highlight">
                    <span class="shop-highlight-icon" aria-hidden="true">02</span>
                    <div>
                        <h2>Chọn món bạn yêu</h2>
                        <p>Khám phá bộ sưu tập dành cho người hâm mộ</p>
                    </div>
                </article>
                <article class="shop-highlight">
                    <span class="shop-highlight-icon" aria-hidden="true">03</span>
                    <div>
                        <h2>Cùng nhau tiếp lửa</h2>
                        <p>You’ll Never Walk Alone — hôm nay và mãi mãi</p>
                    </div>
                </article>
            </section>
        </main>

        <footer class="shop-footer" id="shop-footer">
            <div class="shop-footer-inner">
                <div class="shop-footer-brand">
                    <a class="shop-logo shop-logo-footer" href="<?= BASE_URL ?>" aria-label="LFC SHOP - Trang chủ">
                        <span class="shop-logo-mark" aria-hidden="true">TLC</span>
                        <span class="shop-logo-name"> TUAN LFC <strong>SHOP</strong><small>THE REDS, ALWAYS</small></span>
                    </a>
                    <p>Nơi sắc đỏ và tình yêu Liverpool luôn đồng hành.</p>
                </div>

                <div class="shop-footer-links">
                    <h2>Khám phá</h2>
                    <a href="<?= BASE_URL ?>">Trang chủ</a>
                    <a href="#banner">Bộ sưu tập</a>
                </div>

                <div class="shop-footer-links">
                    <h2>Tài khoản</h2>
                    <?php if (isset($_SESSION['user'])): ?>
                        <span><?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <form method="post" action="<?= BASE_URL ?>?action=logout">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button class="shop-footer-logout" type="submit">Đăng xuất</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>?action=login">Đăng nhập</a>
                        <a href="<?= BASE_URL ?>?action=register">Tạo tài khoản</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="shop-footer-bottom">
                <p>© <?= date('Y') ?> TUAN LFC SHOP. Dành cho những trái tim yêu sắc đỏ.</p>
                <span>You’ll Never Walk Alone</span>
            </div>
        </footer>
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