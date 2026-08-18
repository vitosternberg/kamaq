<?php

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected static string $table = 'settings';
    protected static string $primaryKey = 'key';

    public static function get(string $key, $default = null)
    {
        $row = static::find($key);
        return $row ? $row['value'] : $default;
    }
}
