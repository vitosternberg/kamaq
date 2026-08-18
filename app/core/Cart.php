<?php

namespace App\Core;

use App\Models\Product;
use App\Models\ProductImage;

class Cart
{
    public static function all(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public static function count(): int
    {
        $total = 0;
        foreach (self::all() as $qty) {
            $total += (int) $qty;
        }
        return $total;
    }

    public static function priceOf(array $product): float
    {
        $price = (float) $product['price'];
        if ($product['sale_price'] !== null && (float) $product['sale_price'] > 0) {
            $price = (float) $product['sale_price'];
        }
        return $price;
    }

    public static function items(): array
    {
        $items = [];
        foreach (self::all() as $productId => $qty) {
            $product = Product::find((int) $productId);
            if ($product) {
                $product['cover'] = ProductImage::primary((int) $product['id']);
                $price = self::priceOf($product);
                $items[] = [
                    'product' => $product,
                    'quantity' => (int) $qty,
                    'price' => $price,
                    'subtotal' => $price * (int) $qty,
                ];
            }
        }
        return $items;
    }

    public static function subtotal(): float
    {
        $sum = 0.0;
        foreach (self::items() as $item) {
            $sum += $item['subtotal'];
        }
        return $sum;
    }

    public static function add(int $productId, int $quantity): void
    {
        $cart = self::all();
        $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $quantity);
        $_SESSION['cart'] = $cart;
    }

    public static function update(int $productId, int $quantity): void
    {
        $cart = self::all();
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } elseif (isset($cart[$productId])) {
            $cart[$productId] = max(1, $quantity);
        }
        $_SESSION['cart'] = $cart;
    }

    public static function remove(int $productId): void
    {
        $cart = self::all();
        unset($cart[$productId]);
        $_SESSION['cart'] = $cart;
    }

    public static function clear(): void
    {
        $_SESSION['cart'] = [];
    }
}
