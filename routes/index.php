<?php

$action = $_GET['action'] ?? '/';

match ($action) {
    '/' => (new HomeController())->index(),
    'products' => (new HomeController())->products(),
    'category' => (new HomeController())->category(),
    'product' => (new HomeController())->product(),
    'cart' => (new CartController())->index(),
    'checkout' => (new CartController())->checkout(),
    'payment_qr' => (new CartController())->paymentQr(),
    'orders' => (new OrderController())->index(),
    'order' => (new OrderController())->detail(),
    'order_delete' => (new OrderController())->delete(),
    'login' => (new AuthController())->login(),
    'register' => (new AuthController())->register(),
    'logout' => (new AuthController())->logout(),
    'admin' => (new AdminController())->index(),
    default => http_response_code(404),
};