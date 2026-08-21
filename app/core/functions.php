<?php

// Funciones helper globales para KAMAQ.

function config(string $key, $default = null)
{
    static $config = null;
    if ($config === null) {
        $config = require BASE_PATH . '/app/config.php';
    }
    return $config[$key] ?? $default;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('app_url', ''), '/');
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

function asset(string $path): string
{
    $url = url('assets/' . ltrim($path, '/'));
    $file = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    if (is_file($file)) {
        $url .= '?v=' . filemtime($file);
    }
    return $url;
}

function upload(string $filename): string
{
    return url('uploads/' . ltrim($filename, '/'));
}

function money($amount): string
{
    $decimals = (int) config('currency_decimals', 0);
    $symbol = (string) config('currency_symbol', '$');
    return $symbol . number_format((float) $amount, $decimals, ',', '.');
}

function slugify(string $text): string
{
    $text = function_exists('mb_strtolower')
        ? mb_strtolower(trim($text), 'UTF-8')
        : strtolower(trim($text));

    $map = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ç' => 'c',
    ];
    $text = strtr($text, $map);
    $text = preg_replace('#[^a-z0-9]+#', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function remember_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old'][$key] ?? $default);
}

function send_mail(string $to, string $subject, string $body): bool
{
    $from = (string) config('contact_email', 'contacto@kamaq.cl');
    $headers = "From: " . $from . "\r\n" .
               "Reply-To: " . $from . "\r\n" .
               "MIME-Version: 1.0\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";
    return @mail($to, $subject, $body, $headers);
}
