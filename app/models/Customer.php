<?php

namespace App\Models;

use App\Core\Model;

/**
 * "Clientes" derivados de los pedidos (no hay tabla propia de clientes).
 * Agrupa las órdenes por email y calcula métricas para ticket promedio,
 * remarketing y asistencia.
 */
class Customer extends Model
{
    protected static string $table = 'orders';

    public static function count(): int
    {
        return (int) static::db()->query(
            'SELECT COUNT(DISTINCT customer_email) FROM orders WHERE customer_email <> \'\''
        )->fetchColumn();
    }

    public static function all(): array
    {
        $rows = static::db()->query(
            'SELECT customer_email AS email,
                    MAX(customer_name) AS name,
                    MAX(customer_phone) AS phone,
                    MAX(city) AS city,
                    MAX(region) AS region,
                    COUNT(*) AS order_count,
                    SUM(total) AS total_spent,
                    MIN(created_at) AS first_order,
                    MAX(created_at) AS last_order
             FROM orders
             WHERE customer_email <> \'\'
             GROUP BY customer_email
             ORDER BY last_order DESC'
        )->fetchAll();

        foreach ($rows as $i => $row) {
            $rows[$i] = static::hydrate($row);
        }
        return $rows;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT customer_email AS email,
                    MAX(customer_name) AS name,
                    MAX(customer_phone) AS phone,
                    MAX(city) AS city,
                    MAX(region) AS region,
                    COUNT(*) AS order_count,
                    SUM(total) AS total_spent,
                    MIN(created_at) AS first_order,
                    MAX(created_at) AS last_order
             FROM orders
             WHERE customer_email = ?
             GROUP BY customer_email'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row === false ? null : static::hydrate($row);
    }

    public static function orders(string $email): array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$email]);
        return $stmt->fetchAll();
    }

    // Etiqueta + clase de badge para cada segmento (remarketing).
    public static function segmentMeta(string $key): array
    {
        $map = [
            'vip' => ['VIP', 'vip'],
            'activo' => ['Activo', 'ok'],
            'riesgo' => ['En riesgo', 'warn'],
            'inactivo' => ['Inactivo', 'off'],
        ];
        return $map[$key] ?? ['—', 'off'];
    }

    private static function hydrate(array $row): array
    {
        $row['total_spent'] = (float) $row['total_spent'];
        $row['order_count'] = (int) $row['order_count'];
        $row['avg_ticket'] = $row['order_count'] > 0 ? $row['total_spent'] / $row['order_count'] : 0.0;
        $row['segment'] = self::segment($row['last_order'] ?? null, $row['order_count'], $row['total_spent']);
        return $row;
    }

    private static function segment(?string $lastOrder, int $orderCount, float $totalSpent): string
    {
        // Umbrales editables: VIP por recurrencia o monto acumulado.
        if ($orderCount >= 3 || $totalSpent >= 150000) {
            return 'vip';
        }
        $days = $lastOrder
            ? max(0, (int) floor((time() - strtotime($lastOrder)) / 86400))
            : PHP_INT_MAX;
        if ($days <= 30) {
            return 'activo';
        }
        if ($days <= 90) {
            return 'riesgo';
        }
        return 'inactivo';
    }
}
