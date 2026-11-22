<?php 

session_start();

$cartOwner = isset($_SESSION['user']['id'])
    ? 'user:' . (int) $_SESSION['user']['id']
    : 'guest';

$previousCartOwner = $_SESSION['cart_owner'] ?? null;
if (is_string($previousCartOwner)
    && preg_match('/^user:([1-9][0-9]*)$/', $previousCartOwner, $ownerMatch)
    && isset($_SESSION['cart'])) {
    $_SESSION['user_carts'] ??= [];
    $_SESSION['user_carts'][(int) $ownerMatch[1]] = $_SESSION['cart'];
}

if ($previousCartOwner !== $cartOwner) {
    if (str_starts_with($cartOwner, 'user:')) {
        $userId = (int) substr($cartOwner, 5);
        $_SESSION['cart'] = $_SESSION['user_carts'][$userId] ?? [];
    } else {
        $_SESSION['cart'] = [];
    }
} elseif (!isset($_SESSION['cart'])) {
    if (str_starts_with($cartOwner, 'user:')) {
        $userId = (int) substr($cartOwner, 5);
        $_SESSION['cart'] = $_SESSION['user_carts'][$userId] ?? [];
    } else {
        $_SESSION['cart'] = [];
    }
}
$_SESSION['cart_owner'] = $cartOwner;

spl_autoload_register(function ($class) {    
    $fileName = "$class.php";

    $fileModel              = PATH_MODEL . $fileName;
    $fileController         = PATH_CONTROLLER . $fileName;

    if (is_readable($fileModel)) {
        require_once $fileModel;
    } 
    else if (is_readable($fileController)) {
        require_once $fileController;
    }
});

require_once './configs/env.php';
require_once './configs/helper.php';
require_once PATH_ROOT . 'vendor/autoload.php';

// Điều hướng
require_once './routes/index.php';
