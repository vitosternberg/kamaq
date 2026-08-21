<?php

namespace App\Models;

use App\Core\Model;

class Tax extends Model
{
    protected static string $table = 'taxes';

    // Impuestos activos, ordenados.
    public static function active(): array
    {
        return static::db()->query(
            'SELECT * FROM taxes WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
    }

    // Impuesto por defecto (primer activo).
    public static function default(): ?array
    {
        $all = static::active();
        return $all[0] ?? null;
    }

    // Tasa en porcentaje (ej. 19.0). Si el id no existe o es null, usa el default.
    public static function rate(?int $taxId): float
    {
        if ($taxId !== null) {
            $tax = static::find($taxId);
            if ($tax && !empty($tax['is_active'])) {
                return (float) $tax['rate'];
            }
        }
        $default = static::default();
        return $default ? (float) $default['rate'] : 0.0;
    }
}
