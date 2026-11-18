<?php

class CartController
{
    private const APPAREL_SIZES = ['S', 'M', 'L', 'XL', 'XXL'];

    public function index(): void
    {
        $this->ensureCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $operation = $_POST['operation'] ?? '';
            if ($operation === 'add') {
                $this->addItem();
            } elseif ($operation === 'update' || $operation === 'remove') {
                $this->updateCartItem($operation);
            } else {
                http_response_code(400);
                exit('Thao tác giỏ hàng không hợp lệ.');
            }
        }

        $cartError = $_SESSION['cart_error'] ?? null;
        unset($_SESSION['cart_error']);
        $cartNotice = $_SESSION['cart_notice'] ?? null;
        unset($_SESSION['cart_notice']);
        [$cartLines, $cartTotal] = $this->getCartLines();
        $cartAvailable = !array_filter($cartLines, static fn(array $line): bool => !$line['available']);
        $categories = (new ProductModel())->getStoreCategories();
        $products = [];
        $title = 'Giỏ hàng';
        $isCatalogPage = true;
        $isCartPage = true;
        require PATH_VIEW_MAIN;
    }

    public function checkout(): void
    {
        $this->ensureCsrfToken();
        $cart = $_SESSION['cart'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            [$cartLines, $cartTotal] = $this->getCartLines();
            if ($cartLines === []) {
                $checkoutError = 'Giỏ hàng đang trống.';
            } elseif (array_filter($cartLines, static fn(array $line): bool => !$line['available'])) {
                $checkoutError = 'Tồn kho đã thay đổi. Vui lòng cập nhật giỏ hàng trước khi đặt.';
            } else {
                $customer = $this->checkoutCustomerFromPost();
                $checkoutError = $this->validateCustomer($customer);
                if ($checkoutError === null) {
                    try {
                        $items = array_map(
                            static fn(array $line): array => [
                                'product_id' => (int) $line['product']['id'],
                                'quantity' => (int) $line['quantity'],
                                'size' => $line['size'],
                            ],
                            $cartLines
                        );
                        $userId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
                        $orderCode = (new OrderModel())->createCustomerOrder($customer, $items, $userId);
                        unset($_SESSION['cart']);
                        $_SESSION['order_success'] = $orderCode;
                        header('Location: ' . BASE_URL . '?action=checkout&success=' . urlencode($orderCode));
                        exit;
                    } catch (PDOException $exception) {
                        error_log('Checkout database error: ' . $exception->getMessage());
                        $checkoutError = 'Không thể lưu đơn hàng. Vui lòng kiểm tra cấu trúc bảng đơn hàng, bao gồm cột size trong order_items.';
                    } catch (RuntimeException $exception) {
                        $checkoutError = $exception->getMessage();
                    }
                }
            }
        } else {
            [$cartLines, $cartTotal] = $this->getCartLines();
            $checkoutError = null;
            $customer = $this->checkoutCustomerFromSession();
        }
        $cartAvailable = !array_filter($cartLines, static fn(array $line): bool => !$line['available']);

        $successInput = $_GET['success'] ?? '';
        $sessionOrderCode = $_SESSION['order_success'] ?? null;
        $orderSuccessCode = is_string($successInput)
            && is_string($sessionOrderCode)
            && hash_equals($sessionOrderCode, $successInput)
            && preg_match('/^DH[0-9]{14}[A-F0-9]{6}$/', $successInput)
            ? $successInput
            : null;
        $categories = (new ProductModel())->getStoreCategories();
        $products = [];
        $title = $orderSuccessCode === null ? 'Thanh toán' : 'Đặt hàng thành công';
        $isCatalogPage = true;
        $isCheckoutPage = true;
        require PATH_VIEW_MAIN;
    }

    private function addItem(): void
    {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($productId === false || $productId === null || $productId < 1
            || $quantity === false || $quantity === null || $quantity < 1) {
            $this->redirectWithProductError('Vui lòng chọn số lượng hợp lệ.', $_POST['product_id'] ?? null);
        }

        $product = (new ProductModel())->findAvailableForStore($productId);
        if ($product === null) {
            http_response_code(404);
            exit('Không tìm thấy sản phẩm.');
        }
        if ($quantity > (int) $product['stock']) {
            $this->redirectWithProductError('Số lượng vượt quá hàng còn trong kho.', $productId);
        }

        $sizeInput = $_POST['size'] ?? '';
        $size = is_string($sizeInput) ? $sizeInput : '';
        if (CartModel::requiresSize($product)) {
            if (!in_array($size, self::APPAREL_SIZES, true)) {
                $this->redirectWithProductError('Vui lòng chọn size sản phẩm.', $productId);
            }
        } else {
            $size = '';
        }

        $cart = $_SESSION['cart'] ?? [];
        $currentQuantity = array_sum(array_map('intval', $cart[$productId] ?? []));
        if ($currentQuantity + $quantity > (int) $product['stock']) {
            $this->redirectWithProductError('Tổng số lượng trong giỏ vượt quá hàng còn trong kho.', $productId);
        }
        $cart[$productId][$size] = (int) ($cart[$productId][$size] ?? 0) + $quantity;
        $_SESSION['cart'] = $cart;
        $_SESSION['cart_notice'] = 'Đã thêm sản phẩm vào giỏ hàng.';
        header('Location: ' . BASE_URL . '?action=product&id=' . $productId);
        exit;
    }

    private function updateCartItem(string $operation): void
    {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $sizeInput = $_POST['size'] ?? '';
        $size = is_string($sizeInput) ? $sizeInput : '';
        if ($productId === false || $productId === null || $productId < 1
            || !isset($_SESSION['cart'][$productId])
            || !array_key_exists($size, $_SESSION['cart'][$productId])) {
            $_SESSION['cart_error'] = 'Không tìm thấy sản phẩm trong giỏ.';
            header('Location: ' . BASE_URL . '?action=cart');
            exit;
        }

        if ($operation === 'remove') {
            unset($_SESSION['cart'][$productId][$size]);
            if ($_SESSION['cart'][$productId] === []) {
                unset($_SESSION['cart'][$productId]);
            }
            header('Location: ' . BASE_URL . '?action=cart');
            exit;
        }

        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        $product = (new ProductModel())->findForCart($productId);
        $otherVariantsQuantity = array_sum(array_map(
            'intval',
            array_diff_key($_SESSION['cart'][$productId], [$size => true])
        ));
        if ($quantity === false || $quantity === null || $quantity < 1
            || $product === null || (int) $product['status'] !== 1
            || $otherVariantsQuantity + $quantity > (int) $product['stock']) {
            $_SESSION['cart_error'] = 'Số lượng không hợp lệ hoặc vượt quá hàng còn trong kho.';
        } else {
            $_SESSION['cart'][$productId][$size] = $quantity;
        }
        header('Location: ' . BASE_URL . '?action=cart');
        exit;
    }

    private function getCartLines(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        $productModel = new ProductModel();
        $lines = [];
        $total = 0.0;
        $quantitiesByProduct = [];
        foreach ($cart as $productId => $variants) {
            if (is_array($variants)) {
                $quantitiesByProduct[$productId] = array_sum(array_map('intval', $variants));
            }
        }

        foreach ($cart as $productId => $variants) {
            if (!is_array($variants)) {
                continue;
            }
            $product = $productModel->findForCart((int) $productId);
            if ($product === null) {
                continue;
            }
            foreach ($variants as $size => $quantity) {
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if ($quantity === false || $quantity < 1) {
                    continue;
                }
                $size = is_string($size) ? $size : '';
                if (CartModel::requiresSize($product) && !in_array($size, self::APPAREL_SIZES, true)) {
                    continue;
                }
                $lineTotal = (float) $product['price'] * $quantity;
                $lines[] = [
                    'product' => $product,
                    'size' => $size,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                    'available' => (int) $product['status'] === 1
                        && ($quantitiesByProduct[$productId] ?? $quantity) <= (int) $product['stock'],
                ];
                $total += $lineTotal;
            }
        }

        $cleanCart = [];
        foreach ($lines as $line) {
            $cleanCart[$line['product']['id']][$line['size']] = $line['quantity'];
        }
        if ($cleanCart === []) {
            unset($_SESSION['cart']);
        } else {
            $_SESSION['cart'] = $cleanCart;
        }

        return [$lines, $total];
    }

    private function checkoutCustomerFromPost(): array
    {
        $customer = [];
        foreach (['name', 'email', 'phone', 'address', 'note'] as $field) {
            $value = $_POST[$field] ?? '';
            $customer[$field] = is_string($value) ? trim($value) : '';
        }

        return $customer;
    }

    private function checkoutCustomerFromSession(): array
    {
        $user = $_SESSION['user'] ?? [];

        return [
            'name' => (string) ($user['full_name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'phone' => '',
            'address' => '',
            'note' => '',
        ];
    }

    private function validateCustomer(array $customer): ?string
    {
        if ($customer['name'] === '' || $customer['phone'] === '' || $customer['address'] === '') {
            return 'Vui lòng nhập tên người nhận, số điện thoại và địa chỉ giao hàng.';
        }
        if (!filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Vui lòng nhập email hợp lệ.';
        }
        if (mb_strlen($customer['name']) > 150 || mb_strlen($customer['email']) > 100
            || mb_strlen($customer['phone']) > 20 || mb_strlen($customer['address']) > 500) {
            return 'Thông tin giao hàng vượt quá độ dài cho phép.';
        }

        return null;
    }

    private function ensureCsrfToken(): void
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function validateCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.');
        }
    }

    private function redirectWithProductError(string $message, mixed $productId): never
    {
        $id = filter_var($productId, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            http_response_code(400);
            exit($message);
        }
        $_SESSION['cart_error'] = $message;
        header('Location: ' . BASE_URL . '?action=product&id=' . $id);
        exit;
    }
}
