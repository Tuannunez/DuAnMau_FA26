<?php

class HomeController
{
    public function index() 
    {
        $title = 'Trang chủ';
        $productModel = new ProductModel();
        $products = $productModel->getAvailableForStore();
        $categories = $productModel->getStoreCategories();
        require_once PATH_VIEW_MAIN;
    }

    public function products(): void
    {
        $this->showCatalog();
    }

    public function category(): void
    {
        $categoryId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if ($categoryId === false || $categoryId === null || $categoryId < 1) {
            http_response_code(404);
            exit('Không tìm thấy danh mục.');
        }

        $this->showCatalog($categoryId);
    }

    public function product(): void
    {
        $productId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if ($productId === false || $productId === null || $productId < 1) {
            http_response_code(404);
            exit('Không tìm thấy sản phẩm.');
        }

        $productModel = new ProductModel();
        $product = $productModel->findAvailableForStore($productId);
        if ($product === null) {
            http_response_code(404);
            exit('Không tìm thấy sản phẩm.');
        }

        $categories = $productModel->getStoreCategories();
        $selectedCategoryId = (int) ($product['category_id'] ?? 0);
        $requiresSize = CartModel::requiresSize($product);
        $cartError = $_SESSION['cart_error'] ?? null;
        unset($_SESSION['cart_error']);
        $cartNotice = $_SESSION['cart_notice'] ?? null;
        unset($_SESSION['cart_notice']);
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['csrf_token'];
        $isProductDetail = true;
        $approvedReviews = (new ReviewModel())->getApprovedForProduct($productId);
        $title = $product['name'];
        require PATH_VIEW_MAIN;
    }

    private function showCatalog(?int $categoryId = null): void
    {
        $productModel = new ProductModel();
        $categories = $productModel->getStoreCategories();
        $selectedCategory = $categoryId === null ? null : $productModel->getStoreCategory($categoryId);
        if ($categoryId !== null && $selectedCategory === null) {
            http_response_code(404);
            exit('Không tìm thấy danh mục.');
        }

        $products = $productModel->getAvailableForStore($categoryId);
        $isCatalogPage = true;
        $selectedCategoryId = $categoryId;
        $title = $selectedCategory['name'] ?? 'Sản phẩm';
        require PATH_VIEW_MAIN;
    }
}