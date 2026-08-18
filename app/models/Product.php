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

    public static function byCategory(int $categoryId): array
    {
        $stmt = static::db()->prepare(
            'SELECT p.*, ' . self::COVER_SELECT . ' FROM products p
             WHERE p.category_id = ? AND p.is_active = 1
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
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
