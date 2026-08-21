<?php

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected static string $table = 'categories';

    // Categorías raíz activas (sin padre), para la navegación de la tienda.
    public static function roots(): array
    {
        return static::db()->query(
            'SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order ASC, name ASC'
        )->fetchAll();
    }

    // Hijas activas de una categoría.
    public static function children(int $parentId): array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order ASC, name ASC'
        );
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    // Cadena de ancestros desde la raíz hasta la categoría (incluida ella misma).
    public static function ancestors(?int $categoryId): array
    {
        $chain = [];
        $id = $categoryId;
        $guard = 0;
        while ($id && $guard < 10) {
            $category = static::find($id);
            if (!$category) {
                break;
            }
            array_unshift($chain, $category);
            $id = (int) ($category['parent_id'] ?? 0);
            $guard++;
        }
        return $chain;
    }

    // Todos los descendientes (incluida la propia categoría) — sirve para evitar ciclos al editar.
    public static function descendantIds(int $categoryId): array
    {
        $childrenOf = [];
        foreach (static::all() as $row) {
            $childrenOf[(int) ($row['parent_id'] ?? 0)][] = (int) $row['id'];
        }

        $ids = [];
        $stack = [$categoryId];
        while ($stack) {
            $current = array_pop($stack);
            if (in_array($current, $ids, true)) {
                continue;
            }
            $ids[] = $current;
            foreach ($childrenOf[$current] ?? [] as $child) {
                $stack[] = $child;
            }
        }
        return $ids;
    }

    public static function withCounts(): array
    {
        return static::db()->query(
            'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
             FROM categories c
             ORDER BY c.sort_order ASC, c.name ASC'
        )->fetchAll();
    }

    // Árbol completo (todas las categorías) con su conteo de productos directos.
    public static function treeWithCounts(): array
    {
        $byParent = [];
        foreach (static::withCounts() as $row) {
            $byParent[(int) ($row['parent_id'] ?? 0)][] = $row;
        }

        $build = function (int $parentId) use (&$build, $byParent): array {
            $nodes = [];
            foreach ($byParent[$parentId] ?? [] as $row) {
                $row['children'] = $build((int) $row['id']);
                $nodes[] = $row;
            }
            return $nodes;
        };

        return $build(0);
    }

    // Aplana un árbol en una lista plana con profundidad, para renders <select> o tablas.
    public static function flatten(array $tree, int $depth = 0): array
    {
        $flat = [];
        foreach ($tree as $node) {
            $children = $node['children'] ?? [];
            unset($node['children']);
            $node['depth'] = $depth;
            $flat[] = $node;
            $flat = array_merge($flat, static::flatten($children, $depth + 1));
        }
        return $flat;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = ?';
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
