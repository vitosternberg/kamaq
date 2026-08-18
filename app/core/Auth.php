<?php

namespace App\Core;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = (int) $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            session_regenerate_id(true);
            return true;
        }
        return false;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public static function user(): ?array
    {
        return self::check()
            ? ['id' => (int) $_SESSION['admin_id'], 'name' => $_SESSION['admin_name'] ?? '']
            : null;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_id'], $_SESSION['admin_name']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/admin/login');
        }
    }
}
