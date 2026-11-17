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

        $csrfToken = $this->csrfToken();
        $productModel = null;
        $products = [];
        $categories = [];
        $productForm = $this->emptyProductForm();
        $editingProductId = null;
        $showProductForm = false;
        $additionalImage = null;
        $productError = null;

        if ($section === 'products') {
            $productModel = new ProductModel();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCsrfToken();
                $formAction = $_POST['form_action'] ?? '';

                if ($formAction === 'delete') {
                    $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
                    if ($productId === false || $productId === null || $productId < 1) {
                        $productError = 'Sản phẩm không hợp lệ.';
                    } elseif ($productModel->find($productId) === null) {
                        $productError = 'Không tìm thấy sản phẩm cần xóa.';
                    } else {
                        $productModel->delete($productId);
                        header('Location: ' . BASE_URL . '?action=admin&section=products&result=deleted');
                        exit;
                    }
                } elseif ($formAction === 'save') {
                    $showProductForm = true;
                    $editingProductIdInput = $_POST['product_id'] ?? '';
                    $editingProductId = $editingProductIdInput === ''
                        ? null
                        : filter_var($editingProductIdInput, FILTER_VALIDATE_INT);
                    $productForm = $this->productFormFromPost();

                    if ($editingProductId === false || ($editingProductId !== null && $editingProductId < 1)) {
                        $productError = 'Sản phẩm không hợp lệ.';
                        $editingProductId = null;
                    } else {
                        $productError = $this->validateProductForm($productForm);
                    }

                    $existingProduct = null;
                    if ($productError === null && $editingProductId !== null) {
                        $existingProduct = $productModel->find($editingProductId);
                        if ($existingProduct === null) {
                            $productError = 'Không tìm thấy sản phẩm cần cập nhật.';
                        } else {
                            $productForm['image_url'] = $existingProduct['image_url'];
                            $additionalImage = $productModel->getAdditionalImage($editingProductId);
                        }
                    }

                    if ($productError === null && $productForm['category_id'] !== ''
                        && !$productModel->categoryExists((int) $productForm['category_id'])) {
                        $productError = 'Danh mục đã chọn không tồn tại hoặc đang bị ẩn.';
                    }

                    if ($productError === null) {
                        $uploadedImages = [];
                        try {
                            $uploadedImages = $this->storeProductUploads();
                            $productData = $this->productData(
                                $productForm,
                                $this->uniqueProductSlug($productModel, $productForm['name'], $editingProductId),
                                $uploadedImages['image_1'] ?? ($productForm['image_url'] ?: null)
                            );
                            if ($editingProductId === null) {
                                $productModel->create($productData, $uploadedImages['image_2'] ?? null);
                                $result = 'created';
                            } else {
                                $productModel->update(
                                    $editingProductId,
                                    $productData,
                                    $uploadedImages['image_2'] ?? null,
                                    array_key_exists('image_2', $uploadedImages)
                                );
                                $result = 'updated';
                            }

                            header('Location: ' . BASE_URL . '?action=admin&section=products&result=' . $result);
                            exit;
                        } catch (PDOException $exception) {
                            $this->removeUploadedImages($uploadedImages);
                            if ($exception->getCode() !== '23000') {
                                throw $exception;
                            }
                            $productError = 'Mã sản phẩm đã tồn tại. Vui lòng nhập mã khác.';
                        } catch (RuntimeException $exception) {
                            $this->removeUploadedImages($uploadedImages);
                            $productError = $exception->getMessage();
                        }
                    }
                } else {
                    http_response_code(400);
                    exit('Thao tác không hợp lệ.');
                }
            } else {
                $editingProductIdInput = $_GET['product_id'] ?? null;
                if ($editingProductIdInput !== null) {
                    $showProductForm = true;
                    $editingProductId = filter_var($editingProductIdInput, FILTER_VALIDATE_INT);
                    if ($editingProductId === false || $editingProductId < 1) {
                        http_response_code(400);
                        exit('Mã sản phẩm không hợp lệ.');
                    }
                    $productForm = $productModel->find($editingProductId);
                    if ($productForm === null) {
                        http_response_code(404);
                        exit('Không tìm thấy sản phẩm cần sửa.');
                    }
                    $additionalImage = $productModel->getAdditionalImage($editingProductId);
                } elseif (($_GET['form'] ?? '') === 'create') {
                    $showProductForm = true;
                }
            }

            $productImages = [$productForm['image_url'] ?? null, $additionalImage];
            if ($showProductForm) {
                $categories = $productModel->getCategories();
            } else {
                $products = $productModel->getAll();
            }
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
        if ($section === 'products' && $showProductForm) {
            $title = $editingProductId === null ? 'Thêm sản phẩm' : 'Sửa sản phẩm';
        }

        require PATH_VIEW . 'admin.php';
    }

    private function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function validateCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($this->csrfToken(), $token)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.');
        }
    }

    private function emptyProductForm(): array
    {
        return [
            'category_id' => '',
            'name' => '',
            'sku' => '',
            'description' => '',
            'price' => '',
            'stock' => '0',
            'image_url' => '',
            'status' => '1',
        ];
    }

    private function productFormFromPost(): array
    {
        $form = $this->emptyProductForm();
        foreach ($form as $key => $_) {
            $value = $_POST[$key] ?? '';
            $form[$key] = is_string($value) ? trim($value) : '';
        }

        return $form;
    }

    private function validateProductForm(array $product): ?string
    {
        if ($product['name'] === '' || $product['sku'] === '') {
            return 'Vui lòng nhập tên và mã sản phẩm.';
        }
        if (mb_strlen($product['name']) > 180 || mb_strlen($product['sku']) > 80) {
            return 'Tên hoặc mã sản phẩm vượt quá độ dài cho phép.';
        }
        if (!is_numeric($product['price']) || (float) $product['price'] < 0) {
            return 'Giá bán phải là số không âm.';
        }
        if (filter_var($product['stock'], FILTER_VALIDATE_INT) === false || (int) $product['stock'] < 0) {
            return 'Số lượng phải là số nguyên không âm.';
        }
        if (!in_array($product['status'], ['0', '1'], true)) {
            return 'Trạng thái sản phẩm không hợp lệ.';
        }
        if ($product['category_id'] !== '' && filter_var($product['category_id'], FILTER_VALIDATE_INT) === false) {
            return 'Danh mục không hợp lệ.';
        }

        return null;
    }

    private function productData(array $product, string $slug, ?string $imageUrl): array
    {
        return [
            'category_id' => $product['category_id'] === '' ? null : (int) $product['category_id'],
            'name' => $product['name'],
            'slug' => $slug,
            'sku' => $product['sku'],
            'description' => $product['description'] === '' ? null : $product['description'],
            'price' => $product['price'],
            'stock' => (int) $product['stock'],
            'image_url' => $imageUrl,
            'status' => (int) $product['status'],
        ];
    }

    private function uniqueProductSlug(ProductModel $productModel, string $name, ?int $exceptId): string
    {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $transliterated === false ? $name : $transliterated), '-'));
        $slug = $slug === '' ? 'san-pham' : substr($slug, 0, 180);
        $candidate = $slug;
        $suffix = 2;

        while ($productModel->slugExists($candidate, $exceptId)) {
            $candidate = substr($slug, 0, 180 - strlen((string) $suffix) - 1) . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function storeProductUploads(): array
    {
        $allowedTypes = [
            'image/jpeg' => ['extension' => 'jpg', 'type' => IMAGETYPE_JPEG],
            'image/png' => ['extension' => 'png', 'type' => IMAGETYPE_PNG],
            'image/webp' => ['extension' => 'webp', 'type' => IMAGETYPE_WEBP],
        ];
        $storedImages = [];
        $savedPaths = [];

        try {
            foreach (['image_1', 'image_2'] as $field) {
                if (!isset($_FILES[$field])) {
                    continue;
                }
                $file = $_FILES[$field];
                if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'])) {
                    throw new RuntimeException('Thông tin ảnh tải lên không hợp lệ.');
                }
                if ((int) $file['error'] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('Tải ảnh lên không thành công. Vui lòng chọn lại ảnh.');
                }
                if ((int) $file['size'] > 5 * 1024 * 1024) {
                    throw new RuntimeException('Mỗi ảnh không được vượt quá 5 MB.');
                }
                if (!is_uploaded_file($file['tmp_name'])) {
                    throw new RuntimeException('Tệp ảnh tải lên không hợp lệ.');
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);
                $imageInfo = getimagesize($file['tmp_name']);
                if (!isset($allowedTypes[$mimeType]) || $imageInfo === false || $imageInfo[2] !== $allowedTypes[$mimeType]['type']) {
                    throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.');
                }

                $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType]['extension'];
                $destination = PATH_ASSETS_UPLOADS . $filename;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new RuntimeException('Không thể lưu ảnh vào thư mục tải lên.');
                }

                $savedPaths[] = $destination;
                $storedImages[$field] = BASE_URL . 'assets/uploads/' . $filename;
            }
        } catch (Throwable $exception) {
            foreach ($savedPaths as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
            throw $exception;
        }

        return $storedImages;
    }

    private function removeUploadedImages(array $images): void
    {
        foreach ($images as $imageUrl) {
            $filename = basename((string) parse_url($imageUrl, PHP_URL_PATH));
            if (preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename) === 1) {
                $path = PATH_ASSETS_UPLOADS . $filename;
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }
}
