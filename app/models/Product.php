<?php

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected static string $table = 'products';

    private const COVER_SELECT = "(SELECT pi.filename FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS cover";

    public static function featured(int $limit = 8): array
    {
        $sql = 'SELECT p.*, ' . self::COVER_SELECT . ' FROM products p
                WHERE p.is_active = 1 AND p.is_featured = 1
                ORDER BY p.created_at DESC LIMIT ' . (int) $limit;
        return static::db()->query($sql)->fetchAll();
    }

    // "Destacados del mes": productos marcados como super ventas.
    public static function bestsellers(int $limit = 8): array
    {
        $sql = 'SELECT p.*, ' . self::COVER_SELECT . ' FROM products p
                WHERE p.is_active = 1 AND p.is_bestseller = 1
                ORDER BY p.created_at DESC LIMIT ' . (int) $limit;
        return static::db()->query($sql)->fetchAll();
    }

    // Productos activos en una o varias categorías (por ejemplo, una categoría y sus hijas).
    // Usa la tabla pivote product_categories: incluye los productos asignados a cualquier categoría del listado.
    public static function byCategories(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = static::db()->prepare(
            'SELECT DISTINCT p.*, ' . self::COVER_SELECT . ' FROM products p
             JOIN product_categories pc ON pc.product_id = p.id
             WHERE pc.category_id IN (' . $placeholders . ') AND p.is_active = 1
             ORDER BY p.created_at DESC'
        );
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    // Relaciones categoría↔producto (tabla pivote product_categories).
    public static function categoriesFor(int $id): array
    {
        $stmt = static::db()->prepare('SELECT * FROM product_categories WHERE product_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    // Categorías seleccionadas de un producto: ['ids' => [int, ...], 'primary' => ?int].
    // primary es la fila con is_primary=1, o la primera si no hay ninguna marcada.
    public static function selectedCategoryIds(int $id): array
    {
        $rows = static::categoriesFor($id);
        $ids = array_map(static fn ($row) => (int) $row['category_id'], $rows);
        $primary = null;
        foreach ($rows as $row) {
            if ((int) $row['is_primary'] === 1) {
                $primary = (int) $row['category_id'];
                break;
            }
        }
        if ($primary === null && $ids) {
            $primary = $ids[0];
        }
        return ['ids' => $ids, 'primary' => $primary];
    }

    // Reemplaza las categorías de un producto y denormaliza la principal en products.category_id (back-compat).
    public static function setCategories(int $id, array $categoryIds, ?int $primaryId): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($primaryId !== null) {
            $primaryId = (int) $primaryId;
        }
        if ($primaryId === null || !in_array($primaryId, $ids, true)) {
            $primaryId = $ids[0] ?? null;
        }

        $stmt = static::db()->prepare('DELETE FROM product_categories WHERE product_id = ?');
        $stmt->execute([$id]);

        if ($ids) {
            $insert = static::db()->prepare(
                'INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)'
            );
            foreach ($ids as $cid) {
                $insert->execute([$id, $cid, $cid === $primaryId ? 1 : 0]);
            }
        }

        static::update($id, ['category_id' => $primaryId]);
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM products WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function activeCount(): int
    {
        return (int) static::db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
    }

    public static function paginate(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT p.*, ' . self::COVER_SELECT . ' FROM products p
                WHERE p.is_active = 1
                ORDER BY p.created_at DESC
                LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
        return static::db()->query($sql)->fetchAll();
    }

    public static function search(string $query, int $limit = 24): array
    {
        $like = '%' . $query . '%';
        $stmt = static::db()->prepare(
            'SELECT p.*, ' . self::COVER_SELECT . ' FROM products p
             WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)
             ORDER BY p.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }

    public static function related(int $categoryId, int $excludeId, int $limit = 4): array
    {
        $stmt = static::db()->prepare(
            'SELECT p.*, ' . self::COVER_SELECT . ' FROM products p
             WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
             ORDER BY p.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([$categoryId, $excludeId]);
        return $stmt->fetchAll();
    }

    public static function adminList(): array
    {
        return static::db()->query(
            'SELECT p.*, c.name AS category_name, ' . self::COVER_SELECT . '
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             ORDER BY p.created_at DESC'
        )->fetchAll();
    }

    public static function forSelect(): array
    {
        return static::db()->query(
            'SELECT id, name, sku, price FROM products ORDER BY name ASC'
        )->fetchAll();
    }

    // SKU auto-generado desde el id auto-incremental: SKU-000123
    public static function generateSku(int $id): string
    {
        return 'SKU-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    // Catálogo con filtro por categoría(s) + orden (lista blanca) + paginación.
    public static function catalog(array $categoryIds, string $orderBy, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        // Con categorías filtra por la tabla pivote (un producto puede estar en varias categorías).
        $sql = 'SELECT DISTINCT p.*, ' . self::COVER_SELECT . ' FROM products p';
        $params = [];
        if ($categoryIds) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= ' JOIN product_categories pc ON pc.product_id = p.id AND pc.category_id IN (' . $placeholders . ')';
            $params = array_values($categoryIds);
        }
        $sql .= ' WHERE p.is_active = 1';
        $sql .= ' ORDER BY ' . $orderBy . ' LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function catalogCount(array $categoryIds): int
    {
        $sql = 'SELECT COUNT(DISTINCT p.id) FROM products p';
        $params = [];
        if ($categoryIds) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= ' JOIN product_categories pc ON pc.product_id = p.id AND pc.category_id IN (' . $placeholders . ')';
            $params = array_values($categoryIds);
        }
        $sql .= ' WHERE p.is_active = 1';
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function applyPricePercent(float $percent, ?int $categoryId = null): int
    {
        $factor = 1 + ($percent / 100);
        $sql = 'UPDATE products SET price = ROUND(price * ?, 0),
                sale_price = CASE WHEN sale_price IS NOT NULL THEN ROUND(sale_price * ?, 0) ELSE sale_price END';
        $params = [$factor, $factor];
        if ($categoryId !== null) {
            $sql .= ' WHERE category_id = ?';
            $params[] = $categoryId;
        }
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function adjustStock(int $delta, ?int $categoryId = null): int
    {
        $sql = 'UPDATE products SET stock = GREATEST(stock + ?, 0)';
        $params = [$delta];
        if ($categoryId !== null) {
            $sql .= ' WHERE category_id = ?';
            $params[] = $categoryId;
        }
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function decrementStock(int $productId, int $quantity): void
    {
        $stmt = static::db()->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ? AND track_stock = 1');
        $stmt->execute([$quantity, $productId]);
    }

    public static function incrementStock(int $productId, int $quantity): void
    {
        $stmt = static::db()->prepare('UPDATE products SET stock = stock + ? WHERE id = ? AND track_stock = 1');
        $stmt->execute([$quantity, $productId]);
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE slug = ?';
        $params = [$slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
