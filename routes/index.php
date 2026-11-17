<?php

$action = $_GET['action'] ?? '/';

match ($action) {
    '/' => (new HomeController())->index(),
    'products' => (new HomeController())->products(),
    'category' => (new HomeController())->category(),
    'login' => (new AuthController())->login(),
    'register' => (new AuthController())->register(),
    'logout' => (new AuthController())->logout(),
    'admin' => (new AdminController())->index(),
    default => http_response_code(404),
};