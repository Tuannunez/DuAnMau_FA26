<?php

class AdminController
{
    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Bạn không có quyền truy cập trang quản trị.');
        }

        $navigation = [
            'dashboard' => ['label' => 'Dashboard', 'group' => 'TỔNG QUAN', 'icon' => '▦'],
            'orders' => ['label' => 'Quản lý đơn hàng', 'group' => 'BÁN HÀNG', 'icon' => '▤'],
            'products' => ['label' => 'Sản phẩm', 'group' => 'KHO HÀNG', 'icon' => '◈'],
            'categories' => ['label' => 'Danh mục', 'group' => 'KHO HÀNG', 'icon' => '▧'],
            'customers' => ['label' => 'Quản lý khách hàng', 'group' => 'KHÁCH HÀNG', 'icon' => '♙'],
            'accounts' => ['label' => 'Tài khoản', 'group' => 'KHÁCH HÀNG', 'icon' => '♧'],
            'promotions' => ['label' => 'Khuyến mãi', 'group' => 'MARKETING', 'icon' => '％'],
            'reviews' => ['label' => 'Đánh giá', 'group' => 'MARKETING', 'icon' => '☆'],
            'settings' => ['label' => 'Cài đặt tài khoản', 'group' => 'CÁ NHÂN', 'icon' => '⚙'],
        ];
        $sectionInput = $_GET['section'] ?? 'dashboard';
        $section = is_string($sectionInput) ? $sectionInput : 'dashboard';

        if (!isset($navigation[$section])) {
            http_response_code(404);
            exit('Không tìm thấy mục quản trị.');
        }

        $userModel = new UserModel();
        $stats = $section === 'dashboard' ? $userModel->getAdminDashboardStats() : [];
        $users = match ($section) {
            'dashboard' => $userModel->getUsersForAdmin(null, 8),
            'customers' => $userModel->getUsersForAdmin('customer'),
            'accounts' => $userModel->getUsersForAdmin(),
            default => [],
        };
        $title = $navigation[$section]['label'];
        $csrfToken = $_SESSION['csrf_token'] ?? '';

        require PATH_VIEW . 'admin.php';
    }
}
