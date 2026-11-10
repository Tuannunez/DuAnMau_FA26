<?php

class AuthController
{
    public function login(): void
    {
        $error = null;
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $emailInput = $_POST['email'] ?? '';
            $passwordInput = $_POST['password'] ?? '';
            $email = is_string($emailInput) ? trim($emailInput) : '';
            $password = is_string($passwordInput) ? $passwordInput : '';
            $user = (new UserModel())->findByEmail($email);

            if ($user && (int) $user['status'] === 1 && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role_name'],
                ];

                header('Location: ' . BASE_URL);
                exit;
            }

            $error = 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị khóa.';
        }

        $csrfToken = $this->csrfToken();
        $title = 'Đăng nhập';
        $view = 'login';
        require_once PATH_VIEW_MAIN;
    }

    public function register(): void
    {
        $error = null;
        $fullName = '';
        $email = '';
        $phone = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $fullNameInput = $_POST['full_name'] ?? '';
            $emailInput = $_POST['email'] ?? '';
            $phoneInput = $_POST['phone'] ?? '';
            $fullName = is_string($fullNameInput) ? trim($fullNameInput) : '';
            $email = is_string($emailInput) ? trim($emailInput) : '';
            $phone = is_string($phoneInput) ? trim($phoneInput) : '';
            $phone = $phone === '' ? null : $phone;
            $passwordInput = $_POST['password'] ?? '';
            $confirmationInput = $_POST['password_confirmation'] ?? '';
            $password = is_string($passwordInput) ? $passwordInput : '';
            $passwordConfirmation = is_string($confirmationInput) ? $confirmationInput : '';

            if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Vui lòng nhập họ tên và email hợp lệ.';
            } elseif ($phone !== null && !preg_match('/^[0-9+(). -]{7,15}$/', $phone)) {
                $error = 'Số điện thoại không hợp lệ.';
            } elseif (strlen($password) < 8) {
                $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
            } elseif ($password !== $passwordConfirmation) {
                $error = 'Xác nhận mật khẩu không khớp.';
            } else {
                $userModel = new UserModel();

                if ($userModel->emailOrPhoneExists($email, $phone)) {
                    $error = 'Email hoặc số điện thoại đã được sử dụng.';
                } else {
                    $userModel->createCustomer(
                        $fullName,
                        $email,
                        $phone,
                        password_hash($password, PASSWORD_DEFAULT)
                    );

                    header('Location: ' . BASE_URL . '?action=login&registered=1');
                    exit;
                }
            }
        }

        $csrfToken = $this->csrfToken();
        $title = 'Đăng ký';
        $view = 'register';
        require_once PATH_VIEW_MAIN;
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Phương thức không được hỗ trợ.');
        }

        $this->validateCsrfToken();
        unset($_SESSION['user']);
        session_regenerate_id(true);

        header('Location: ' . BASE_URL);
        exit;
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
}
