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

        $userModel = new UserModel();
        $stats = $userModel->getAdminDashboardStats();
        $users = $userModel->getLatestUsersForAdmin();
        $csrfToken = $_SESSION['csrf_token'] ?? '';

        require PATH_VIEW . 'admin.php';
    }
}
