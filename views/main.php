<?php
$cartItemCount = 0;
foreach (($_SESSION['cart'] ?? []) as $cartVariants) {
    if (is_array($cartVariants)) {
        foreach ($cartVariants as $quantity) {
            $cartItemCount += max(0, (int) $quantity);
        }
    }
}
?>
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
        <link href="<?= BASE_URL ?>assets/home.css?v=<?= filemtime(PATH_ROOT . 'assets/home.css') ?>" rel="stylesheet">
        <script src="<?= BASE_URL ?>assets/home.js?v=<?= filemtime(PATH_ROOT . 'assets/home.js') ?>" defer></script>
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
                    <a
                        class="shop-nav-link <?= empty($isCatalogPage) && empty($isProductDetail) ? 'is-active' : '' ?>"
                        href="<?= BASE_URL ?>">
                        Trang chủ
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a
                            class="shop-nav-link <?= isset($selectedCategoryId) && $selectedCategoryId === (int) $category['id'] ? 'is-active' : '' ?>"
                            href="<?= BASE_URL ?>?action=category&amp;id=<?= (int) $category['id'] ?>">
                            <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <?php if (empty($isProductDetail) && empty($isCartPage) && empty($isCheckoutPage)): ?>
                    <form class="shop-search" role="search" data-category-search>
                        <label class="visually-hidden" for="shop-search-input">Tìm kiếm sản phẩm</label>
                        <input id="shop-search-input" type="search" placeholder="Tìm kiếm..." autocomplete="off">
                        <button type="submit" aria-label="Tìm kiếm">
                            <span aria-hidden="true">⌕</span>
                        </button>
                    </form>
                <?php endif; ?>

                <div class="shop-account-actions">
                    <a class="shop-cart-link" href="<?= BASE_URL ?>?action=cart">
                        Giỏ hàng <span class="shop-cart-count"><?= $cartItemCount ?></span>
                    </a>
                    <span class="shop-account-divider" aria-hidden="true"></span>
                    <?php if (isset($_SESSION['user'])): ?>
                        <span class="shop-account-name">
                            <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                                <a class="shop-admin-link" href="<?= BASE_URL ?>?action=admin">
                                    <?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                    <small>Quản trị</small>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($_SESSION['user']['full_name'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
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
            <?php if (!empty($isProductDetail)): ?>
                <?php require PATH_VIEW . 'partials/store-product-detail.php'; ?>
            <?php elseif (!empty($isCartPage)): ?>
                <?php require PATH_VIEW . 'partials/store-cart.php'; ?>
            <?php elseif (!empty($isCheckoutPage)): ?>
                <?php require PATH_VIEW . 'partials/store-checkout.php'; ?>
            <?php else: ?>
            <?php if (empty($isCatalogPage)): ?>
                <section class="shop-hero" id="banner" aria-label="Bộ sưu tập LFC Shop">
                    <img
                        class="shop-hero-image is-active"
                        src="<?= BASE_URL ?>assets/images/banner1.png"
                        alt="LFC Shop - You'll Never Walk Alone, hàng chính hãng, ưu đãi hấp dẫn, giao hàng nhanh"
                        fetchpriority="high">
                    <img
                        class="shop-hero-image"
                        src="<?= BASE_URL ?>assets/images/banner2.png"
                        alt="Bộ sưu tập áo bóng đá Liverpool và đồ lưu niệm"
                        loading="lazy"
                        aria-hidden="true">
                    <img
                        class="shop-hero-image"
                        src="<?= BASE_URL ?>assets/images/banner3.png"
                        alt="Bộ sưu tập Liverpool và Adidas"
                        loading="lazy"
                        aria-hidden="true">
                </section>
            <?php endif; ?>

            <section class="shop-products" id="shop-products" aria-labelledby="shop-products-title">
                <div class="shop-section-heading">
                    <div>
                        <span class="shop-section-kicker">DÀNH CHO CÁC REDS</span>
                        <h1 id="shop-products-title"><?= htmlspecialchars(!empty($isCatalogPage) ? $title : 'Khám phá sản phẩm', ENT_QUOTES, 'UTF-8') ?></h1>
                    </div>
                    <p class="shop-search-status" data-search-status aria-live="polite"></p>
                </div>

                <?php if ($products === []): ?>
                    <p class="shop-search-empty">Hiện chưa có sản phẩm đang bán.</p>
                <?php else: ?>
                    <div class="shop-product-grid" data-product-list>
                        <?php foreach ($products as $product): ?>
                            <?php
                            $searchTerms = implode(' ', [
                                $product['name'],
                                $product['category_name'] ?? '',
                                $product['sku'],
                                $product['description'] ?? '',
                            ]);
                            ?>
                            <article
                                class="shop-product-card"
                                data-product-card
                                data-category-id="<?= (int) ($product['category_id'] ?? 0) ?>"
                                data-search-terms="<?= htmlspecialchars($searchTerms, ENT_QUOTES, 'UTF-8') ?>">
                                <a class="shop-product-card-link" href="<?= BASE_URL ?>?action=product&amp;id=<?= (int) $product['id'] ?>">
                                    <div class="shop-product-image">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img
                                                src="<?= htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                loading="lazy">
                                        <?php else: ?>
                                            <span>Chưa có hình ảnh</span>
                                        <?php endif; ?>
                                        <?php if (!empty($product['additional_image'])): ?>
                                            <img
                                                class="shop-product-image-secondary"
                                                src="<?= htmlspecialchars($product['additional_image'], ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?> - ảnh phụ"
                                                loading="lazy">
                                        <?php endif; ?>
                                        <span class="shop-product-view-detail">Xem chi tiết <span aria-hidden="true">→</span></span>
                                    </div>
                                    <div class="shop-product-info">
                                        <?php if (!empty($product['category_name'])): ?>
                                            <span class="shop-product-category"><?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <h2><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                                        <p class="shop-product-price"><?= number_format((float) $product['price'], 0, ',', '.') ?> ₫</p>
                                        <p class="shop-product-stock">
                                            <?= (int) $product['stock'] > 0 ? 'Còn ' . (int) $product['stock'] . ' sản phẩm' : 'Tạm hết hàng' ?>
                                        </p>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                        <p class="shop-search-empty" data-search-empty hidden>Không tìm thấy sản phẩm phù hợp.</p>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if (empty($isCartPage) && empty($isCheckoutPage)): ?>
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
            <?php endif; ?>
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