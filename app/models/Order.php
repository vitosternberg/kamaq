<?php

namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected static string $table = 'orders';

    public static function recent(int $limit = 5): array
    {
        $stmt = static::db()->prepare('SELECT * FROM orders ORDER BY created_at DESC LIMIT ' . (int) $limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function withItems(int $id): ?array
    {
        $order = static::find($id);
        if (!$order) {
            return null;
        }
        $order['items'] = OrderItem::forOrder($id);
        return $order;
    }
}
