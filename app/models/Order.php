<?php

namespace App\Models;

use App\Core\Model;
use App\Models\Dte;

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

    public static function findByToken(string $token): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM orders WHERE transbank_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
        $stmt->execute([$orderNumber]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    // Búsqueda pública de seguimiento: nº de pedido + email del cliente.
    public static function findByOrderNumberAndEmail(string $orderNumber, string $email): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM orders WHERE order_number = ? AND customer_email = ? LIMIT 1');
        $stmt->execute([$orderNumber, $email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    private static function customerScopeSql(): string
    {
        return '(customer_id = ? OR (customer_id IS NULL AND customer_email = ?))';
    }

    public static function forCustomer(int $customerId, string $email): array
    {
        $sql = 'SELECT * FROM orders WHERE ' . self::customerScopeSql() . ' ORDER BY created_at DESC';
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$customerId, $email]);
        return $stmt->fetchAll();
    }

    public static function findForCustomer(int $orderId, int $customerId, string $email): ?array
    {
        $sql = 'SELECT id FROM orders WHERE id = ? AND ' . self::customerScopeSql() . ' LIMIT 1';
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$orderId, $customerId, $email]);
        if ($stmt->fetch() === false) {
            return null;
        }
        return static::withItems($orderId);
    }

    public static function statsForCustomer(int $customerId, string $email): array
    {
        $sql = 'SELECT COUNT(*) AS order_count,
                       COALESCE(SUM(total), 0) AS total_spent,
                       MIN(created_at) AS first_order,
                       MAX(created_at) AS last_order
                FROM orders
                WHERE ' . self::customerScopeSql() . "
                  AND status <> 'cancelado'";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$customerId, $email]);
        $row = $stmt->fetch();
        if ($row === false) {
            return [
                'order_count' => 0,
                'total_spent' => 0.0,
                'avg_ticket' => 0.0,
                'first_order' => null,
                'last_order' => null,
            ];
        }
        $count = (int) $row['order_count'];
        $total = (float) $row['total_spent'];
        return [
            'order_count' => $count,
            'total_spent' => $total,
            'avg_ticket' => $count > 0 ? $total / $count : 0.0,
            'first_order' => $row['first_order'],
            'last_order' => $row['last_order'],
        ];
    }

    public static function topProductsForCustomer(int $customerId, string $email, int $limit = 8): array
    {
        $limit = max(1, min(20, $limit));
        $sql = 'SELECT oi.product_id,
                       oi.product_name,
                       SUM(oi.quantity) AS total_qty,
                       SUM(oi.subtotal) AS total_spent
                FROM order_items oi
                INNER JOIN orders o ON o.id = oi.order_id
                WHERE ' . self::customerScopeSql() . "
                  AND o.status <> 'cancelado'
                GROUP BY oi.product_id, oi.product_name
                ORDER BY total_qty DESC
                LIMIT {$limit}";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$customerId, $email]);
        return $stmt->fetchAll();
    }

    // Normaliza la fecha de Transbank (ISO 8601, con fracción y zona horaria) a DATETIME MySQL.
    private static function normalizeTbkDate($transactionDate): ?string
    {
        $d = (string) ($transactionDate ?? '');
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2}:\d{2})/', $d, $m)) {
            return $m[1] . ' ' . $m[2];
        }
        return null;
    }

    public static function markPaid(int $id, array $tbk): void
    {
        static::update($id, [
            'payment_status' => 'pagado',
            'status' => 'pagado',
            'paid_at' => date('Y-m-d H:i:s'),
            'transbank_authorization_code' => $tbk['authorization_code'] ?? null,
            'transbank_payment_type' => $tbk['payment_type_code'] ?? null,
            'transbank_transaction_date' => self::normalizeTbkDate($tbk['transaction_date'] ?? null),
            'transbank_installments' => (int) ($tbk['installments_number'] ?? 0),
        ]);

        // Emisión de DTE no-bloqueante: si falla, no debe romper el pago.
        try {
            Dte::emitForOrder($id);
        } catch (\Throwable $e) {
            error_log('Dte: emisión tras pago falló: ' . $e->getMessage());
        }
    }

    public static function markRejected(int $id): void
    {
        static::update($id, ['payment_status' => 'rechazado']);
    }

    // Pago anulado/abandonado por el usuario en el formulario de Webpay.
    public static function markCancelled(int $id): void
    {
        static::update($id, ['payment_status' => 'cancelado', 'status' => 'cancelado']);
    }

    // Devuelve al inventario el stock reservado de un pedido.
    public static function releaseStock(int $orderId): void
    {
        foreach (OrderItem::forOrder($orderId) as $item) {
            if ($item['product_id'] === null) {
                continue;
            }
            Product::incrementStock((int) $item['product_id'], (int) $item['quantity']);
        }
    }

    /**
     * Barrido lazy (se invoca en cada request): expira pedidos pendientes cuyo
     * vencimiento (expires_at) ya pasó, liberando su stock.
     * Usa un UPDATE "claim-first" para evitar doble liberación.
     */
    public static function expireUnpaid(): int
    {
        // Sin control de stock: no se expiran/cancelan pedidos pendientes
        // automáticamente ni se libera inventario.
        return 0;
    }
}
