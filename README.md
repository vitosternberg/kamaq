# KAMAQ — Tienda en línea

Tienda de regalos y mementos personalizados: regalos corporativos, bautizos,
baby shower, matrimonios, cumpleaños, cajas de vino y joyeros.

Stack: **PHP 8 + MySQL**, sin framework, apto para hosting compartido.

## Requisitos

- PHP 8.0 o superior
- Extensiones: `pdo_mysql`, `mbstring`, `fileinfo`
- MySQL 5.7+ / MariaDB 10.3+
- Apache con `mod_rewrite` (para URLs amigables)

## Instalación

1. **Configurar la base de datos** — edita `app/config.local.php` con tus credenciales:
   ```php
   return [
       'db_host' => 'localhost',
       'db_port' => '3306',
       'db_name' => 'kamaq',
       'db_user' => 'tu_usuario',
       'db_pass' => 'tu_clave',
   ];
   ```

2. **Crear tablas y usuario admin** (por línea de comandos):
   ```bash
   php database/install.php
   ```
   Esto crea las tablas, las categorías base y te pide los datos del usuario
   administrador (por defecto `admin@kamaq.cl` / `admin123` — cámbialo).

3. **Apuntar el sitio a `public/`** (elige una opción):
   - **Opción A (recomendada):** configura el dominio/document root del hosting
     hacia la carpeta `public/`.
   - **Opción B:** sube todo el proyecto a la raíz (`public_html/`). El archivo
     `.htaccess` de la raíz enruta automáticamente todo hacia `public/`.

4. **Permisos** — asegúrate de que `public/uploads/products/` tenga permisos de
   escritura (se crea automáticamente al subir la primera imagen).

## Acceso al panel

- URL: `/admin`
- Usuario: el que creaste en el instalador.

## Estructura

```
app/
  controllers/       Controladores (públicos y Admin/)
  core/              Núcleo: Router, Database, Model, Controller, Auth, Cart, helpers
  models/            Modelos (Category, Product, Order, …)
  views/             Vistas (layout, públicas, admin)
  config.php         Configuración base
  config.local.php   Credenciales locales (no versionado)
database/
  schema.sql         Esquema completo + datos base
  install.php        Instalador por CLI
public/              Web root (index.php + assets + uploads)
docs/backlog.md      Backlog del proyecto
```

## Notas

- **SEO**: URLs amigables (`/producto/{slug}`, `/categoria/{slug}`), metadatos por
  página/producto/categoría, y etiqueta Google Ads (gtag.js) configurable en
  `app/config.php` (`ga_ads_id`).
- **Pago**: PagaAquí (BancoEstado) en CLP. La integración de la pasarela queda
  pendiente (ver `docs/backlog.md`, BK-405).
- **Moneda**: CLP por defecto; el símbolo y decimales se configuran en
  `app/config.php`.
