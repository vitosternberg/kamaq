<?php

namespace App\Models;

use App\Core\Model;

class ShippingMethod extends Model
{
    protected static string $table = 'shipping_methods';

    public static function active(): array
    {
        return static::db()->query(
            'SELECT * FROM shipping_methods WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
    }
}
