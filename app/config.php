<?php

// Configuración base de KAMAQ.
// Los valores locales (credenciales de BD) van en config.local.php (no versionado).

$config = [
    'app_name' => 'KAMAQ',
    'app_url' => '',               // '' si el sitio está en la raíz del dominio
    'maintenance_mode' => false,   // true = sitio en mantención (solo /admin queda accesible)
    'launch_at' => '2026-08-21 23:59:00', // fecha/hora del reloj de lanzamiento
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'kamaq',
    'db_user' => 'root',
    'db_pass' => '',
    'db_charset' => 'utf8mb4',
    'currency' => 'CLP',
    'currency_symbol' => '$',
    'currency_decimals' => 0,
    'contact_email' => 'contacto@kamaq.cl',
    'whatsapp' => '56932080779', // WhatsApp (con código país, para wa.me)
    'ga_ads_id' => 'AW-18397361572', // Google Ads (gtag.js)
    'ga_ads_conversion_id' => 'AW-18397361572/sU7OCKPp7-McEKTrxcRE', // Conversión: Compra
];

$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (is_array($local)) {
        $config = array_merge($config, $local);
    }
}

return $config;
