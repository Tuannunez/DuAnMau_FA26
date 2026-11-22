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
        $categoryForm = $this->emptyCategoryForm();
        $editingCategoryId = null;
        $showCategoryForm = false;
        $categoryError = null;
        $categoryRows = [];
        $parentCategories = [];
        $orders = [];
        $order = null;
        $orderItems = [];
        $orderError = null;
        $orderStatusFilter = null;
        $orderStatuses = OrderModel::statuses();
        $paymentStatuses = OrderModel::paymentStatuses();
        $orderResult = null;
        $promotions = [];
        $promotionError = null;
        $promotionResult = null;
        $promotionForm = [
            'code' => '',
            'name' => '',
            'description' => '',
            'discount_type' => 'percentage',
            'discount_value' => '',
            'starts_at' => '',
            'ends_at' => '',
            'status' => '1',
        ];
        $editingPromotionId = null;
        $showPromotionForm = false;
        $reviews = [];
        $reviewError = null;
        $reviewResult = null;

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
                            if ($editingProductId === null
                                && !isset($uploadedImages['image_1'])
                                && isset($uploadedImages['image_2'])) {
                                $uploadedImages['image_1'] = $uploadedImages['image_2'];
                                unset($uploadedImages['image_2']);
                            }
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

        if ($section === 'categories') {
            $categoryModel = new CategoryModel();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCsrfToken();
                $formAction = $_POST['form_action'] ?? '';

                if ($formAction === 'delete') {
                    $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
                    if ($categoryId === false || $categoryId === null || $categoryId < 1) {
                        $categoryError = 'Danh mục không hợp lệ.';
                    } else {
                        $category = $categoryModel->find($categoryId);
                        if ($category === null) {
                            $categoryError = 'Không tìm thấy danh mục cần xóa.';
                        } else {
                            $references = $categoryModel->getReferenceCounts($categoryId);
                            if ((int) $references['products'] > 0) {
                                $categoryError = 'Không thể xóa danh mục đang có sản phẩm. Hãy chuyển sản phẩm sang danh mục khác trước.';
                            } elseif ((int) $references['children'] > 0) {
                                $categoryError = 'Không thể xóa danh mục đang có danh mục con. Hãy chuyển danh mục con trước.';
                            } else {
                                $categoryModel->delete($categoryId);
                                header('Location: ' . BASE_URL . '?action=admin&section=categories&result=deleted');
                                exit;
                            }
                        }
                    }
                } elseif ($formAction === 'save') {
                    $showCategoryForm = true;
                    $editingCategoryIdInput = $_POST['category_id'] ?? '';
                    $editingCategoryId = $editingCategoryIdInput === ''
                        ? null
                        : filter_var($editingCategoryIdInput, FILTER_VALIDATE_INT);
                    $categoryForm = $this->categoryFormFromPost();

                    if ($editingCategoryId === false || ($editingCategoryId !== null && $editingCategoryId < 1)) {
                        $categoryError = 'Danh mục không hợp lệ.';
                        $editingCategoryId = null;
                    } else {
                        $categoryError = $this->validateCategoryForm($categoryForm);
                    }

                    if ($categoryError === null && $editingCategoryId !== null
                        && $categoryModel->find($editingCategoryId) === null) {
                        $categoryError = 'Không tìm thấy danh mục cần cập nhật.';
                    }
                    if ($categoryError === null && $categoryForm['parent_id'] !== ''
                        && !$categoryModel->parentExists((int) $categoryForm['parent_id'], $editingCategoryId)) {
                        $categoryError = 'Danh mục cha không hợp lệ hoặc bị trùng với chính nó.';
                    }

                    if ($categoryError === null) {
                        $slug = $this->uniqueCategorySlug(
                            $categoryModel,
                            $categoryForm['name'],
                            $editingCategoryId
                        );
                        if ($categoryModel->nameExists($categoryForm['name'], $editingCategoryId)) {
                            $categoryError = 'Tên danh mục đã tồn tại. Vui lòng chọn tên khác.';
                        } else {
                            $categoryData = [
                                'parent_id' => $categoryForm['parent_id'] === '' ? null : (int) $categoryForm['parent_id'],
                                'name' => $categoryForm['name'],
                                'slug' => $slug,
                                'description' => $categoryForm['description'] === '' ? null : $categoryForm['description'],
                                'status' => (int) $categoryForm['status'],
                            ];

                            try {
                                if ($editingCategoryId === null) {
                                    $categoryModel->create($categoryData);
                                    $result = 'created';
                                } else {
                                    $categoryModel->update($editingCategoryId, $categoryData);
                                    $result = 'updated';
                                }
                                header('Location: ' . BASE_URL . '?action=admin&section=categories&result=' . $result);
                                exit;
                            } catch (PDOException $exception) {
                                if ($exception->getCode() !== '23000') {
                                    throw $exception;
                                }
                                $categoryError = 'Tên danh mục hoặc đường dẫn đã tồn tại.';
                            }
                        }
                    }
                } else {
                    http_response_code(400);
                    exit('Thao tác danh mục không hợp lệ.');
                }
            } else {
                $editingCategoryIdInput = $_GET['category_id'] ?? null;
                if ($editingCategoryIdInput !== null) {
                    $showCategoryForm = true;
                    $editingCategoryId = filter_var($editingCategoryIdInput, FILTER_VALIDATE_INT);
                    if ($editingCategoryId === false || $editingCategoryId < 1) {
                        http_response_code(400);
                        exit('Mã danh mục không hợp lệ.');
                    }
                    $categoryForm = $categoryModel->find($editingCategoryId);
                    if ($categoryForm === null) {
                        http_response_code(404);
                        exit('Không tìm thấy danh mục cần sửa.');
                    }
                } elseif (($_GET['form'] ?? '') === 'create') {
                    $showCategoryForm = true;
                }
            }

            if ($showCategoryForm) {
                $parentCategories = $categoryModel->getParentOptions($editingCategoryId);
            } else {
                $categoryRows = $categoryModel->getAll();
            }
        }

        if ($section === 'promotions') {
            $promotionModel = new PromotionModel();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCsrfToken();
                $formAction = $_POST['form_action'] ?? '';

                if ($formAction === 'save') {
                    $showPromotionForm = true;
                    $promotionIdInput = $_POST['promotion_id'] ?? '';
                    $editingPromotionId = $promotionIdInput === ''
                        ? null
                        : filter_var($promotionIdInput, FILTER_VALIDATE_INT);
                    foreach (array_keys($promotionForm) as $key) {
                        $value = $_POST[$key] ?? '';
                        $promotionForm[$key] = is_string($value) ? trim($value) : '';
                    }

                    if ($editingPromotionId === false || ($editingPromotionId !== null && $editingPromotionId < 1)) {
                        $promotionError = 'Mã khuyến mãi không hợp lệ.';
                    } else {
                        $promotionError = $this->validatePromotionForm($promotionForm);
                    }
                    if ($promotionError === null) {
                        if ($editingPromotionId !== null && $promotionModel->find($editingPromotionId) === null) {
                            $promotionError = 'Không tìm thấy khuyến mãi cần cập nhật.';
                        }
                    }
                    if ($promotionError === null) {
                        try {
                            $data = $this->promotionDataFromForm($promotionForm);
                            if ($editingPromotionId === null) {
                                $promotionModel->create($data);
                                $result = 'created';
                            } else {
                                $promotionModel->update($editingPromotionId, $data);
                                $result = 'updated';
                            }
                            header('Location: ' . BASE_URL . '?action=admin&section=promotions&result=' . $result);
                            exit;
                        } catch (PDOException $exception) {
                            error_log('Admin promotion save database error: ' . $exception->getMessage());
                            $promotionError = $exception->getCode() === '23000'
                                ? 'Mã khuyến mãi đã tồn tại.'
                                : 'Không thể lưu khuyến mãi. Lỗi cơ sở dữ liệu: ' . $exception->getMessage();
                        }
                    }
                } elseif ($formAction === 'delete') {
                    $promotionId = filter_var($_POST['promotion_id'] ?? null, FILTER_VALIDATE_INT);
                    if ($promotionId === false || $promotionId === null || $promotionId < 1) {
                        $promotionError = 'Mã khuyến mãi không hợp lệ.';
                    } else {
                        $promotionModel->delete($promotionId);
                        header('Location: ' . BASE_URL . '?action=admin&section=promotions&result=deleted');
                        exit;
                    }
                } elseif ($formAction === 'toggle') {
                    $promotionId = filter_var($_POST['promotion_id'] ?? null, FILTER_VALIDATE_INT);
                    if ($promotionId === false || $promotionId === null || $promotionId < 1) {
                        $promotionError = 'Mã khuyến mãi không hợp lệ.';
                    } else {
                        $promotionModel->toggleStatus($promotionId);
                        header('Location: ' . BASE_URL . '?action=admin&section=promotions&result=updated');
                        exit;
                    }
                } else {
                    http_response_code(400);
                    exit('Thao tác khuyến mãi không hợp lệ.');
                }
            } else {
                $promotionIdInput = $_GET['promotion_id'] ?? null;
                if ($promotionIdInput !== null) {
                    $showPromotionForm = true;
                    $editingPromotionId = filter_var($promotionIdInput, FILTER_VALIDATE_INT);
                    if ($editingPromotionId === false || $editingPromotionId < 1) {
                        http_response_code(400);
                        exit('Mã khuyến mãi không hợp lệ.');
                    }
                    $row = $promotionModel->find($editingPromotionId);
                    if ($row === null) {
                        http_response_code(404);
                        exit('Không tìm thấy khuyến mãi.');
                    }
                    $promotionForm = [
                        'code' => $row['code'],
                        'name' => $row['name'],
                        'description' => $row['description'] ?? '',
                        'discount_type' => $row['discount_type'],
                        'discount_value' => (string) $row['discount_value'],
                        'starts_at' => $row['starts_at'] ? date('Y-m-d\TH:i', strtotime($row['starts_at'])) : '',
                        'ends_at' => $row['ends_at'] ? date('Y-m-d\TH:i', strtotime($row['ends_at'])) : '',
                        'status' => (string) $row['status'],
                    ];
                } elseif (($_GET['form'] ?? '') === 'create') {
                    $showPromotionForm = true;
                }
            }

            if (!$showPromotionForm) {
                $promotions = $promotionModel->getAll();
            }
            $promotionResultMessages = [
                'created' => 'Đã thêm khuyến mãi.',
                'updated' => 'Đã cập nhật khuyến mãi.',
                'deleted' => 'Đã xóa khuyến mãi.',
            ];
            $promotionResultInput = $_GET['result'] ?? '';
            $promotionResult = is_string($promotionResultInput)
                ? ($promotionResultMessages[$promotionResultInput] ?? null)
                : null;
        }

        if ($section === 'reviews') {
            $reviewModel = new ReviewModel();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCsrfToken();
                $reviewId = filter_var($_POST['review_id'] ?? null, FILTER_VALIDATE_INT);
                if ($reviewId === false || $reviewId === null || $reviewId < 1) {
                    $reviewError = 'Mã đánh giá không hợp lệ.';
                } else {
                    $formAction = $_POST['form_action'] ?? '';
                    if ($formAction === 'approve') {
                        $reviewModel->setStatus($reviewId, 'approved');
                        header('Location: ' . BASE_URL . '?action=admin&section=reviews&result=approved');
                        exit;
                    }
                    if ($formAction === 'hide') {
                        $reviewModel->setStatus($reviewId, 'hidden');
                        header('Location: ' . BASE_URL . '?action=admin&section=reviews&result=hidden');
                        exit;
                    }
                    if ($formAction === 'delete') {
                        $reviewModel->delete($reviewId);
                        header('Location: ' . BASE_URL . '?action=admin&section=reviews&result=deleted');
                        exit;
                    }
                    http_response_code(400);
                    exit('Thao tác đánh giá không hợp lệ.');
                }
            }

            $reviews = $reviewModel->getAll();
            $reviewResultMessages = [
                'approved' => 'Đã duyệt đánh giá.',
                'hidden' => 'Đã ẩn đánh giá.',
                'deleted' => 'Đã xóa đánh giá.',
            ];
            $reviewResultInput = $_GET['result'] ?? '';
            $reviewResult = is_string($reviewResultInput)
                ? ($reviewResultMessages[$reviewResultInput] ?? null)
                : null;
        }

        if ($section === 'orders') {
            $orderModel = new OrderModel();
            $orderIdInput = $_GET['order_id'] ?? null;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCsrfToken();
                $formAction = $_POST['form_action'] ?? '';
                $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);

                if ($formAction !== 'update_status') {
                    http_response_code(400);
                    exit('Thao tác đơn hàng không hợp lệ.');
                }
                if ($orderId === false || $orderId === null || $orderId < 1) {
                    http_response_code(400);
                    exit('Mã đơn hàng không hợp lệ.');
                }
                if ($orderModel->find($orderId) === null) {
                    http_response_code(404);
                    exit('Không tìm thấy đơn hàng.');
                }

                $statusInput = $_POST['status'] ?? '';
                $paymentStatusInput = $_POST['payment_status'] ?? '';
                if (!is_string($statusInput) || !in_array($statusInput, $orderStatuses, true)
                    || !is_string($paymentStatusInput) || !in_array($paymentStatusInput, $paymentStatuses, true)) {
                    $orderError = 'Vui lòng chọn trạng thái đơn hàng và thanh toán hợp lệ.';
                    $order = $orderModel->find($orderId);
                    $orderItems = $orderModel->getItems($orderId);
                } else {
                    $orderModel->updateStatus($orderId, $statusInput, $paymentStatusInput);
                    header('Location: ' . BASE_URL . '?action=admin&section=orders&order_id=' . $orderId . '&result=updated');
                    exit;
                }
            } elseif ($orderIdInput !== null) {
                $orderId = filter_var($orderIdInput, FILTER_VALIDATE_INT);
                if ($orderId === false || $orderId < 1) {
                    http_response_code(400);
                    exit('Mã đơn hàng không hợp lệ.');
                }

                $order = $orderModel->find($orderId);
                if ($order === null) {
                    http_response_code(404);
                    exit('Không tìm thấy đơn hàng.');
                }
                $orderItems = $orderModel->getItems($orderId);
            } else {
                $statusInput = $_GET['status'] ?? '';
                if ($statusInput !== '') {
                    if (!is_string($statusInput) || !in_array($statusInput, $orderStatuses, true)) {
                        http_response_code(400);
                        exit('Bộ lọc trạng thái đơn hàng không hợp lệ.');
                    }
                    $orderStatusFilter = $statusInput;
                }
                $orders = $orderModel->getAll($orderStatusFilter);
            }

            $resultInput = $_GET['result'] ?? '';
            $orderResult = $resultInput === 'updated' ? 'Đã cập nhật trạng thái đơn hàng.' : null;
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
        } elseif ($section === 'categories' && $showCategoryForm) {
            $title = $editingCategoryId === null ? 'Thêm danh mục' : 'Sửa danh mục';
        } elseif ($section === 'orders' && $order !== null) {
            $title = 'Chi tiết đơn hàng';
        }
        if ($section === 'promotions' && $showPromotionForm) {
            $title = $editingPromotionId === null ? 'Thêm khuyến mãi' : 'Sửa khuyến mãi';
        }

        require PATH_VIEW . 'admin.php';
    }

    private function validatePromotionForm(array $promotion): ?string
    {
        if ($promotion['code'] === '' || $promotion['name'] === '') {
            return 'Vui lòng nhập mã và tên khuyến mãi.';
        }
        if (!preg_match('/^[A-Z0-9_-]{2,30}$/', strtoupper($promotion['code']))) {
            return 'Mã khuyến mãi chỉ gồm 2-30 ký tự chữ, số, gạch ngang hoặc gạch dưới.';
        }
        if (!in_array($promotion['discount_type'], ['percentage', 'fixed'], true)) {
            return 'Loại giảm giá không hợp lệ.';
        }
        $value = filter_var($promotion['discount_value'], FILTER_VALIDATE_FLOAT);
        if ($value === false || $value <= 0) {
            return 'Giá trị giảm giá phải lớn hơn 0.';
        }
        if ($promotion['discount_type'] === 'percentage' && $value > 100) {
            return 'Giảm theo phần trăm không được vượt quá 100%.';
        }
        if (!in_array($promotion['status'], ['0', '1'], true)) {
            return 'Trạng thái khuyến mãi không hợp lệ.';
        }
        if ($promotion['starts_at'] !== '' && strtotime($promotion['starts_at']) === false) {
            return 'Thời gian bắt đầu không hợp lệ.';
        }
        if ($promotion['ends_at'] !== '' && strtotime($promotion['ends_at']) === false) {
            return 'Thời gian kết thúc không hợp lệ.';
        }
        if ($promotion['starts_at'] !== '' && $promotion['ends_at'] !== ''
            && strtotime($promotion['ends_at']) < strtotime($promotion['starts_at'])) {
            return 'Thời gian kết thúc phải sau thời gian bắt đầu.';
        }
        return null;
    }

    private function promotionDataFromForm(array $promotion): array
    {
        $promotion['code'] = strtoupper($promotion['code']);
        $promotion['discount_value'] = (float) $promotion['discount_value'];
        $promotion['starts_at'] = $promotion['starts_at'] !== '' ? date('Y-m-d H:i:s', strtotime($promotion['starts_at'])) : null;
        $promotion['ends_at'] = $promotion['ends_at'] !== '' ? date('Y-m-d H:i:s', strtotime($promotion['ends_at'])) : null;
        $promotion['status'] = (int) $promotion['status'];
        return $promotion;
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

    private function emptyCategoryForm(): array
    {
        return [
            'parent_id' => '',
            'name' => '',
            'description' => '',
            'status' => '1',
        ];
    }

    private function categoryFormFromPost(): array
    {
        $form = $this->emptyCategoryForm();
        foreach ($form as $key => $_) {
            $value = $_POST[$key] ?? '';
            $form[$key] = is_string($value) ? trim($value) : '';
        }

        return $form;
    }

    private function validateCategoryForm(array $category): ?string
    {
        if ($category['name'] === '') {
            return 'Vui lòng nhập tên danh mục.';
        }
        if (mb_strlen($category['name']) > 150) {
            return 'Tên danh mục không được vượt quá 150 ký tự.';
        }
        if (mb_strlen($category['description']) > 5000) {
            return 'Mô tả danh mục quá dài.';
        }
        if (!in_array($category['status'], ['0', '1'], true)) {
            return 'Trạng thái danh mục không hợp lệ.';
        }
        if ($category['parent_id'] !== ''
            && filter_var($category['parent_id'], FILTER_VALIDATE_INT) === false) {
            return 'Danh mục cha không hợp lệ.';
        }

        return null;
    }

    private function uniqueCategorySlug(CategoryModel $categoryModel, string $name, ?int $exceptId): string
    {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $slug = strtolower(trim((string) preg_replace(
            '/[^a-zA-Z0-9]+/',
            '-',
            $transliterated === false ? $name : $transliterated
        ), '-'));
        $slug = $slug === '' ? 'danh-muc' : substr($slug, 0, 170);
        $candidate = $slug;
        $suffix = 2;

        while ($categoryModel->slugExists($candidate, $exceptId)) {
            $candidate = substr($slug, 0, 170 - strlen((string) $suffix) - 1) . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
