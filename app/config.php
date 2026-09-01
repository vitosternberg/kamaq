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

    // Transbank Webpay Plus (credenciales de INTEGRACIÓN; producción se sobrescribe en config.local.php).
    'tbk_env' => 'integration',
    'tbk_api_base' => 'https://webpay3gint.transbank.cl',
    'tbk_commerce_code' => '597055555532',
    'tbk_api_key_id' => '597055555532',
    'tbk_api_key_secret' => '579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C',
    // LibreDTE (boleta/factura electrónica) — ambiente CERTIFICACIÓN.
    // libredte_hash se completa en config.local.php (placeholder '' = pendiente de configuración).
    'libredte_api_base' => 'https://libredte.cl/api',
    'libredte_hash' => '',   // placeholder; se completa en config.local.php
    'libredte_certificacion' => true,
    'emisor_rut' => '78.479.102-5',
    'emisor_razon_social' => 'KAMAQ COMERCIAL Y SERVICIOS DE DISEÑO SPA',
    'emisor_giro' => 'Comercio al por menor y por mayor de artículos y actividades de diseño',
    'emisor_acteco' => '',
    'emisor_direccion' => 'ANTONIO BELLET 193 OF 1210',
    'emisor_comuna' => 'Providencia',
    'emisor_ciudad' => 'Santiago',
    'emisor_telefono' => '',
    'emisor_email' => 'ereyes@kamaq.cl',
    'dte_email_pdf' => true,
];

$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (is_array($local)) {
        $config = array_merge($config, $local);
    }
}

return $config;
