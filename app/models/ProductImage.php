<?php

namespace App\Models;

use App\Core\Model;

class ProductImage extends Model
{
    protected static string $table = 'product_images';

    public static function forProduct(int $productId): array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC'
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public static function primary(int $productId): ?string
    {
        $stmt = static::db()->prepare(
            'SELECT filename FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 1'
        );
        $stmt->execute([$productId]);
        $row = $stmt->fetch();
        return $row ? $row['filename'] : null;
    }
}
