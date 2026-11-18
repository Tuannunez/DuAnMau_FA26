<?php

define('BASE_URL',          'http://localhost/BaseExam/');

define('PATH_ROOT',         __DIR__ . '/../');

define('PATH_VIEW',         PATH_ROOT . 'views/');

define('PATH_VIEW_MAIN',    PATH_ROOT . 'views/main.php');

define('BASE_ASSETS_UPLOADS',   BASE_URL . 'assets/uploads/');

define('PATH_ASSETS_UPLOADS',   PATH_ROOT . 'assets/uploads/');

define('PATH_CONTROLLER',       PATH_ROOT . 'controllers/');

define('PATH_MODEL',            PATH_ROOT . 'models/');

define('DB_HOST',     'localhost');
define('DB_PORT',     '3306');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME',     'duanmau_fa26');
$paymentLocalConfigPath = PATH_ROOT . 'configs/payment.local.php';
$paymentLocalConfig = is_file($paymentLocalConfigPath) ? require $paymentLocalConfigPath : [];
define('BANK_TRANSFER_BANK_NAME', getenv('BANK_TRANSFER_BANK_NAME') ?: ($paymentLocalConfig['bank_name'] ?? ''));
define('BANK_TRANSFER_BANK_BIN', getenv('BANK_TRANSFER_BANK_BIN') ?: ($paymentLocalConfig['bank_bin'] ?? ''));
define('BANK_TRANSFER_ACCOUNT_NUMBER', getenv('BANK_TRANSFER_ACCOUNT_NUMBER') ?: ($paymentLocalConfig['account_number'] ?? ''));
define('BANK_TRANSFER_ACCOUNT_HOLDER', getenv('BANK_TRANSFER_ACCOUNT_HOLDER') ?: ($paymentLocalConfig['account_holder'] ?? ''));
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
