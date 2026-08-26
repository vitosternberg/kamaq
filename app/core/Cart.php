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
                $taxId = ($product['tax_id'] ?? null) !== null ? (int) $product['tax_id'] : null;
                $subtotal = $price * (int) $qty;
                $items[] = [
                    'product' => $product,
                    'quantity' => (int) $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'tax_id' => $taxId,
                    'tax' => $subtotal * tax_rate($taxId) / 100,
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

    // Total de impuestos del carrito.
    public static function tax(): float
    {
        $sum = 0.0;
        foreach (self::items() as $item) {
            $sum += $item['tax'];
        }
        return $sum;
    }

    // Peso total del carrito en kg (producto sin peso = 1 g).
    public static function weight(): float
    {
        $total = 0.0;
        foreach (self::items() as $item) {
            $w = $item['product']['weight'] ?? null;
            $kg = ($w !== null && $w !== '') ? (float) $w : 0.001;
            $total += $kg * (int) $item['quantity'];
        }
        return $total;
    }

    public static function add(int $productId, int $quantity): void
    {
        $cart = self::all();
        $stock = self::stockOf($productId);
        if ($stock <= 0) {
            return;
        }
        $current = (int) ($cart[$productId] ?? 0);
        $cart[$productId] = min($current + max(1, $quantity), $stock);
        $_SESSION['cart'] = $cart;
    }

    public static function update(int $productId, int $quantity): void
    {
        $cart = self::all();
        $stock = self::stockOf($productId);
        if ($quantity <= 0 || $stock <= 0) {
            unset($cart[$productId]);
        } elseif (isset($cart[$productId])) {
            $cart[$productId] = min(max(1, $quantity), $stock);
        }
        $_SESSION['cart'] = $cart;
    }

    public static function quantityOf(int $productId): int
    {
        return (int) (self::all()[$productId] ?? 0);
    }

    private static function stockOf(int $productId): int
    {
        $product = Product::find($productId);
        return $product ? max(0, (int) $product['stock']) : 0;
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
