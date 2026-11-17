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