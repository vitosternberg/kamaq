<?php

namespace App\Models;

/**
 * Envío por tramo de peso (XS/S/M/L) y zona geográfica (Naranja/Celeste/Azul),
 * con dos modalidades: Despacho a domicilio y PUDO (pickup / drop-off).
 * Matriz Blue Express (CLP). El tope de cada tramo es exclusivo (1 g de separación).
 */
class Shipping
{
    // Tope superior de cada tramo (kg). XS: 0–0.5, S: 0.5–3, M: 3–6, L: 6–20.
    public const TIER_MAX = ['XS' => 0.5, 'S' => 3.0, 'M' => 6.0, 'L' => 20.0];

    public const TIERS = ['XS', 'S', 'M', 'L'];
    public const ZONES = ['naranja', 'celeste', 'azul'];
    public const MODALITIES = ['domicilio', 'pudo'];

    // Regiones por zona (desde Santiago como origen).
    public const ZONE_REGIONS = [
        'naranja' => ['Metropolitana de Santiago'],
        'celeste' => ['Coquimbo', 'Valparaíso', "O'Higgins", 'Maule', 'Ñuble', 'Biobío', 'La Araucanía', 'Los Ríos', 'Los Lagos'],
        'azul'    => ['Arica y Parinacota', 'Tarapacá', 'Antofagasta', 'Atacama', 'Aysén', 'Magallanes'],
    ];

    // Matriz de tarifas: [modalidad][zona][talla] => CLP.
    public const RATES = [
        'domicilio' => [
            'naranja' => ['XS' => 3100, 'S' => 4200, 'M' => 4800, 'L' => 5400],
            'celeste' => ['XS' => 4300, 'S' => 5600, 'M' => 7300, 'L' => 9200],
            'azul'    => ['XS' => 5200, 'S' => 9500, 'M' => 14500, 'L' => 17000],
        ],
        'pudo' => [
            'naranja' => ['XS' => 2600, 'S' => 3700, 'M' => 4300, 'L' => 4900],
            'celeste' => ['XS' => 3800, 'S' => 5100, 'M' => 6800, 'L' => 8700],
            'azul'    => ['XS' => 4700, 'S' => 9000, 'M' => 14000, 'L' => 16500],
        ],
    ];

    // Matriz vigente: lee desde settings (admin/envios) y usa RATES como fallback.
    public static function rates(): array
    {
        $stored = Setting::get('shipping_rates', '');
        $data = ($stored !== '' && $stored !== null) ? json_decode((string) $stored, true) : null;
        if (!is_array($data)) {
            return self::RATES;
        }
        $merged = self::RATES;
        foreach (self::MODALITIES as $m) {
            foreach (self::ZONES as $z) {
                foreach (self::TIERS as $t) {
                    if (isset($data[$m][$z][$t]) && is_numeric($data[$m][$z][$t])) {
                        $merged[$m][$z][$t] = (int) $data[$m][$z][$t];
                    }
                }
            }
        }
        return $merged;
    }

    // Talla según peso total en kg.
    public static function tierOf(float $kg): string
    {
        if ($kg < 0.5) {
            return 'XS';
        }
        if ($kg < 3.0) {
            return 'S';
        }
        if ($kg < 6.0) {
            return 'M';
        }
        return 'L';
    }

    // Zona según región del cliente.
    public static function zoneOf(string $region): string
    {
        $region = trim($region);
        foreach (self::ZONE_REGIONS as $zone => $regions) {
            if (in_array($region, $regions, true)) {
                return $zone;
            }
        }
        return 'azul'; // por defecto, zona extremo (la más cara)
    }

    // Opciones de envío (modalidades) para una región y peso.
    public static function options(string $region, float $weightKg): array
    {
        $zone = self::zoneOf($region);
        $tier = self::tierOf($weightKg);
        $rates = self::rates();
        return [
            [
                'key' => 'domicilio',
                'name' => 'Despacho a domicilio',
                'price' => (float) $rates['domicilio'][$zone][$tier],
                'zone' => $zone,
                'tier' => $tier,
            ],
            [
                'key' => 'pudo',
                'name' => 'PUDO (retiro en punto Blue Express / Copec)',
                'price' => (float) $rates['pudo'][$zone][$tier],
                'zone' => $zone,
                'tier' => $tier,
            ],
        ];
    }

    public static function priceFor(string $key, string $region, float $weightKg): float
    {
        foreach (self::options($region, $weightKg) as $option) {
            if ($option['key'] === $key) {
                return $option['price'];
            }
        }
        $all = self::options($region, $weightKg);
        return $all ? $all[0]['price'] : 0.0;
    }

    // Etiquetas legibles.
    public static function zoneLabel(string $zone): string
    {
        $map = [
            'naranja' => 'Naranja (Santiago / misma zona)',
            'celeste' => 'Celeste (regiones centro)',
            'azul'    => 'Azul Oscuro (zonas extremas)',
        ];
        return $map[$zone] ?? $zone;
    }

    public static function tierLabel(string $tier): string
    {
        $map = [
            'XS' => 'XS (0 – 0,5 kg)',
            'S'  => 'S (0,5 – 3 kg)',
            'M'  => 'M (3 – 6 kg)',
            'L'  => 'L (6 – 20 kg)',
        ];
        return $map[$tier] ?? $tier;
    }

    public static function modalityLabel(string $modality): string
    {
        $map = [
            'domicilio' => 'Despacho a domicilio',
            'pudo'      => 'PUDO (pickup / drop-off)',
        ];
        return $map[$modality] ?? $modality;
    }
}
