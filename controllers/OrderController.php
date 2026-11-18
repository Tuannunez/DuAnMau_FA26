<?php

class OrderController
{
    private const ORDER_STATUS_LABELS = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'completed' => 'Đã giao thành công',
        'cancelled' => 'Đã hủy',
    ];

    private const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
    ];

    public function index(): void
    {
        $this->ensureCsrfToken();
        $orderModel = new OrderModel();
        $orders = $orderModel->getCustomerOrders($this->currentUserId(), $this->guestOrderCodes());
        $orderNotice = $_SESSION['order_notice'] ?? null;
        unset($_SESSION['order_notice']);
        $orderNoticeSuccess = is_string($orderNotice) && str_starts_with($orderNotice, 'Đã xóa');
        $csrfToken = $_SESSION['csrf_token'];
        $orderStatusLabels = self::ORDER_STATUS_LABELS;
        $paymentStatusLabels = self::PAYMENT_STATUS_LABELS;
        $paymentMethodLabels = [
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
        ];
        $categories = (new ProductModel())->getStoreCategories();
        $products = [];
        $title = 'Đơn hàng của tôi';
        $isCatalogPage = true;
        $isOrdersPage = true;
        require PATH_VIEW_MAIN;
    }

    public function detail(): void
    {
        $this->ensureCsrfToken();
        $code = $_GET['code'] ?? '';
        if (!is_string($code) || !preg_match('/^DH[0-9]{14}[A-F0-9]{6}$/', $code)) {
            http_response_code(404);
            exit('Không tìm thấy đơn hàng.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findCustomerOrder(
            $code,
            $this->currentUserId(),
            $this->guestOrderCodes()
        );
        if ($order === null) {
            http_response_code(404);
            exit('Không tìm thấy đơn hàng.');
        }

        $orderItems = $orderModel->getItems((int) $order['id']);
        $csrfToken = $_SESSION['csrf_token'];
        $canDeleteOrder = in_array($order['status'], ['completed', 'cancelled'], true);
        $orderStatusLabels = self::ORDER_STATUS_LABELS;
        $paymentStatusLabels = self::PAYMENT_STATUS_LABELS;
        $paymentMethodLabels = [
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
        ];
        $categories = (new ProductModel())->getStoreCategories();
        $products = [];
        $title = 'Chi tiết đơn hàng ' . $order['order_code'];
        $isCatalogPage = true;
        $isOrdersPage = true;
        require PATH_VIEW_MAIN;
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Phương thức không được hỗ trợ.');
        }
        $this->ensureCsrfToken();
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.');
        }

        $code = $_POST['order_code'] ?? '';
        if (!is_string($code) || !preg_match('/^DH[0-9]{14}[A-F0-9]{6}$/', $code)) {
            http_response_code(400);
            exit('Mã đơn hàng không hợp lệ.');
        }

        try {
            $deleted = (new OrderModel())->deleteCustomerOrder(
                $code,
                $this->currentUserId(),
                $this->guestOrderCodes()
            );
            if ($deleted) {
                $_SESSION['guest_order_codes'] = array_values(array_filter(
                    $this->guestOrderCodes(),
                    static fn($guestCode): bool => $guestCode !== $code
                ));
                if (is_array($_SESSION['order_success'] ?? null)
                    && ($_SESSION['order_success']['code'] ?? null) === $code) {
                    unset($_SESSION['order_success']);
                }
                $_SESSION['order_notice'] = 'Đã xóa đơn hàng.';
            } else {
                $_SESSION['order_notice'] = 'Chỉ có thể xóa đơn đã giao hoặc đã hủy thuộc về bạn.';
            }
        } catch (PDOException $exception) {
            error_log('Customer order delete database error: ' . $exception->getMessage());
            $_SESSION['order_notice'] = 'Chưa thể xóa đơn hàng. Vui lòng thử lại sau.';
        }

        header('Location: ' . BASE_URL . '?action=orders');
        exit;
    }

    private function currentUserId(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    private function guestOrderCodes(): array
    {
        $codes = $_SESSION['guest_order_codes'] ?? [];

        return is_array($codes) ? $codes : [];
    }

    private function ensureCsrfToken(): void
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}
