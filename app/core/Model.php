<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function count(): int
    {
        return (int) static::db()->query('SELECT COUNT(*) FROM `' . static::$table . '`')->fetchColumn();
    }

    public static function all(string $orderBy = ''): array
    {
        $sql = 'SELECT * FROM `' . static::$table . '`';
        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        return static::db()->query($sql)->fetchAll();
    }

    public static function find(int|string $id): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $data): int
    {
        $cols = implode(', ', array_map(fn ($k) => '`' . $k . '`', array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = static::db()->prepare(
            'INSERT INTO `' . static::$table . '` (' . $cols . ') VALUES (' . $placeholders . ')'
        );
        $stmt->execute(array_values($data));
        return (int) static::db()->lastInsertId();
    }

    public static function update(int|string $id, array $data): void
    {
        $set = implode(', ', array_map(fn ($k) => '`' . $k . '` = ?', array_keys($data)));
        $stmt = static::db()->prepare(
            'UPDATE `' . static::$table . '` SET ' . $set . ' WHERE `' . static::$primaryKey . '` = ?'
        );
        $stmt->execute([...array_values($data), $id]);
    }

    public static function delete(int|string $id): void
    {
        $stmt = static::db()->prepare(
            'DELETE FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ?'
        );
        $stmt->execute([$id]);
    }
}
