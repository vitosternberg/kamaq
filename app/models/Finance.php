<?php

namespace App\Models;

use App\Core\Model;

/**
 * Métricas financieras básicas para el dashboard: ingresos (cobrados)
 * y costo variable de lo vendido, en un período dado.
 */
class Finance extends Model
{
    protected static string $table = 'orders';

    // Estados que cuentan como dinero realmente cobrado.
    private const PAID = "('pagado', 'enviado', 'entregado')";

    public static function revenue(?string $since = null): float
    {
        $sql = 'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status IN ' . self::PAID;
        $params = [];
        if ($since) {
            $sql .= ' AND created_at >= ?';
            $params[] = $since;
        }
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public static function variableCost(?string $since = null): float
    {
        $sql = 'SELECT COALESCE(SUM(oi.cost * oi.quantity), 0)
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                WHERE o.status IN ' . self::PAID;
        $params = [];
        if ($since) {
            $sql .= ' AND o.created_at >= ?';
            $params[] = $since;
        }
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }
}
