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

// URL absoluta (con dominio), para enlaces en correos y notificaciones.
function absolute_url(string $path = ''): string
{
    $base = rtrim((string) config('app_url', ''), '/');
    if ($base !== '') {
        return $base . '/' . ltrim($path, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return $scheme . '://' . $host . '/' . ltrim($path, '/');
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

function chile_communes(): array
{
    return [
        'Arica y Parinacota' => ['Arica', 'Camarones', 'Putre', 'General Lagos'],
        'Tarapacá' => ['Iquique', 'Alto Hospicio', 'Pozo Almonte', 'Camiña', 'Colchane', 'Huara', 'Pica'],
        'Antofagasta' => ['Antofagasta', 'Mejillones', 'Sierra Gorda', 'Taltal', 'Calama', 'Ollagüe', 'San Pedro de Atacama', 'Tocopilla', 'María Elena'],
        'Atacama' => ['Copiapó', 'Caldera', 'Tierra Amarilla', 'Chañaral', 'Diego de Almagro', 'Vallenar', 'Alto del Carmen', 'Freirina', 'Huasco'],
        'Coquimbo' => ['La Serena', 'Coquimbo', 'Andacollo', 'La Higuera', 'Paihuano', 'Vicuña', 'Illapel', 'Canela', 'Los Vilos', 'Salamanca', 'Ovalle', 'Combarbalá', 'Monte Patria', 'Punitaqui', 'Río Hurtado'],
        'Valparaíso' => ['Valparaíso', 'Casablanca', 'Concón', 'Juan Fernández', 'Puchuncaví', 'Quintero', 'Viña del Mar', 'Isla de Pascua', 'Los Andes', 'Calle Larga', 'Rinconada', 'San Esteban', 'La Ligua', 'Cabildo', 'Papudo', 'Petorca', 'Zapallar', 'Quillota', 'La Calera', 'Hijuelas', 'La Cruz', 'Nogales', 'San Antonio', 'Algarrobo', 'Cartagena', 'El Quisco', 'El Tabo', 'Santo Domingo', 'San Felipe', 'Catemu', 'Llaillay', 'Panquehue', 'Putaendo', 'Santa María', 'Limache', 'Olmué', 'Quilpué', 'Villa Alemana'],
        'Metropolitana de Santiago' => ['Santiago', 'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central', 'Huechuraba', 'Independencia', 'La Cisterna', 'La Florida', 'La Granja', 'La Pintana', 'La Reina', 'Las Condes', 'Lo Barnechea', 'Lo Espejo', 'Lo Prado', 'Macul', 'Maipú', 'Ñuñoa', 'Pedro Aguirre Cerda', 'Peñalolén', 'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal', 'Recoleta', 'Renca', 'San Joaquín', 'San Miguel', 'San Ramón', 'Vitacura', 'Puente Alto', 'Pirque', 'San José de Maipo', 'Colina', 'Lampa', 'Tiltil', 'San Bernardo', 'Buin', 'Calera de Tango', 'Paine', 'Melipilla', 'Alhué', 'Curacaví', 'María Pinto', 'San Pedro', 'Talagante', 'El Monte', 'Isla de Maipo', 'Padre Hurtado', 'Peñaflor'],
        "O'Higgins" => ['Rancagua', 'Codegua', 'Coinco', 'Coltauco', 'Doñihue', 'Graneros', 'Las Cabras', 'Machalí', 'Malloa', 'Mostazal', 'Olivar', 'Peumo', 'Pichidegua', 'Quinta de Tilcoco', 'Rengo', 'Requínoa', 'San Vicente', 'Pichilemu', 'La Estrella', 'Litueche', 'Marchigüe', 'Navidad', 'Paredones', 'San Fernando', 'Chépica', 'Chimbarongo', 'Lolol', 'Nancagua', 'Palmilla', 'Peralillo', 'Placilla', 'Pumanque', 'Santa Cruz'],
        'Maule' => ['Talca', 'Constitución', 'Curepto', 'Empedrado', 'Maule', 'Pelarco', 'Pencahue', 'Río Claro', 'San Clemente', 'San Rafael', 'Cauquenes', 'Chanco', 'Pelluhue', 'Curicó', 'Hualañé', 'Licantén', 'Molina', 'Rauco', 'Romeral', 'Sagrada Familia', 'Teno', 'Vichuquén', 'Linares', 'Colbún', 'Longaví', 'Parral', 'Retiro', 'San Javier', 'Villa Alegre', 'Yerbas Buenas'],
        'Ñuble' => ['Chillán', 'Bulnes', 'Cobquecura', 'Coelemu', 'Coihueco', 'Chillán Viejo', 'El Carmen', 'Ninhue', 'Ñiquén', 'Pemuco', 'Pinto', 'Portezuelo', 'Quillón', 'Quirihue', 'Ránquil', 'San Carlos', 'San Fabián', 'San Ignacio', 'San Nicolás', 'Treguaco', 'Yungay'],
        'Biobío' => ['Concepción', 'Coronel', 'Chiguayante', 'Florida', 'Hualqui', 'Lota', 'Penco', 'San Pedro de la Paz', 'Santa Juana', 'Talcahuano', 'Tomé', 'Hualpén', 'Lebu', 'Arauco', 'Cañete', 'Contulmo', 'Curanilahue', 'Los Álamos', 'Tirúa', 'Los Ángeles', 'Antuco', 'Cabrero', 'Laja', 'Mulchén', 'Nacimiento', 'Negrete', 'Quilaco', 'Quilleco', 'San Rosendo', 'Santa Bárbara', 'Tucapel', 'Yumbel', 'Alto Biobío'],
        'La Araucanía' => ['Temuco', 'Carahue', 'Cunco', 'Curarrehue', 'Freire', 'Galvarino', 'Gorbea', 'Lautaro', 'Loncoche', 'Melipeuco', 'Nueva Imperial', 'Padre Las Casas', 'Perquenco', 'Pitrufquén', 'Pucón', 'Saavedra', 'Teodoro Schmidt', 'Toltén', 'Vilcún', 'Villarrica', 'Cholchol', 'Angol', 'Collipulli', 'Curacautín', 'Ercilla', 'Lonquimay', 'Los Sauces', 'Lumaco', 'Purén', 'Renaico', 'Traiguén', 'Victoria'],
        'Los Ríos' => ['Valdivia', 'Corral', 'Lanco', 'Los Lagos', 'Máfil', 'Mariquina', 'Paillaco', 'Panguipulli', 'La Unión', 'Futrono', 'Lago Ranco', 'Río Bueno'],
        'Los Lagos' => ['Puerto Montt', 'Calbuco', 'Cochamó', 'Fresia', 'Frutillar', 'Los Muermos', 'Llanquihue', 'Maullín', 'Puerto Varas', 'Castro', 'Ancud', 'Chonchi', 'Curaco de Vélez', 'Dalcahue', 'Puqueldón', 'Queilén', 'Quellón', 'Quemchi', 'Quinchao', 'Osorno', 'Puerto Octay', 'Purranque', 'Puyehue', 'Río Negro', 'San Juan de la Costa', 'San Pablo', 'Chaitén', 'Futaleufú', 'Hualaihué', 'Palena'],
        'Aysén' => ['Coyhaique', 'Lago Verde', 'Aysén', 'Cisnes', 'Guaitecas', 'Chile Chico', 'Río Ibáñez', 'Cochrane', "O'Higgins", 'Tortel'],
        'Magallanes' => ['Punta Arenas', 'Laguna Blanca', 'Río Verde', 'San Gregorio', 'Cabo de Hornos', 'Antártica', 'Porvenir', 'Primavera', 'Timaukel', 'Natales', 'Torres del Paine'],
    ];
}

function chile_regions(): array
{
    return array_keys(chile_communes());
}

function is_rm_region(string $region): bool
{
    return $region === 'Metropolitana de Santiago';
}
