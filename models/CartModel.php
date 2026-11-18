<?php

class CartModel
{
    public static function requiresSize(array $product): bool
    {
        $categoryName = mb_strtolower((string) ($product['category_name'] ?? ''), 'UTF-8');

        return str_contains($categoryName, 'áo') || str_contains($categoryName, 'quần');
    }
}
