# Backlog — Tienda KAMAQ

Tienda en línea de regalos y mementos personalizados: regalos corporativos,
bautizos, baby shower, matrimonios, cumpleaños, cajas de vino y joyeros.

- **Stack**: PHP 8 + MySQL, sin framework, apto para hosting compartido.
- **Prioridad**: `P0` imprescindible · `P1` importante · `P2` deseable · `P3` futuro.
- **Estado**: `Por hacer` · `En progreso` · `Hecho`.

> Decisiones de negocio cerradas: pago en línea con **PagaAquí (BancoEstado)**
> en **CLP** (MVP1); país **Chile**. Multi-moneda queda para MVP2 (BK-407).

---

## 1. Fundamentos del proyecto (núcleo MVC + base de datos)

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-101 | P0 | Hecho | Estructura MVC base (`app/core`, `app/controllers`, `app/models`, `app/views`, `public/`) | Navegación por URLs amigables funcionando |
| BK-102 | P0 | Hecho | Conexión PDO + configuración (`app/config.php` + `config.local.php`) | Conexión a MySQL con credenciales locales no versionadas |
| BK-103 | P0 | Hecho | Tabla `settings` con moneda, símbolo y datos del sitio | Moneda/símbolo configurables sin tocar código |
| BK-104 | P0 | Hecho | Esquema SQL completo (`database/schema.sql`) | Todas las tablas creadas con `utf8mb4` e InnoDB |
| BK-105 | P0 | Hecho | Datos semilla: categorías raíz + admin inicial (`database/seed.sql` o instalador) | Sitio arranca con categorías precargadas y acceso admin |
| BK-106 | P1 | Hecho | Instalador CLI (`database/install.php`) para crear tablas y admin | Instalación reproducible con un solo comando |
| BK-107 | P1 | En progreso | Manejo de errores 404 / 500 | Página de error amigable, sin exponer detalles (404 listo; 500 mínimo) |

## 2. Panel de administración (mantenedores)

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-201 | P0 | Hecho | Login/logout de administrador (sesión + CSRF) | Solo usuarios autenticados acceden a `/admin` |
| BK-202 | P0 | Hecho | Dashboard con resumen (productos, categorías, pedidos) | Contadores y últimos pedidos visibles |
| BK-203 | P0 | Hecho | Mantenedor de **categorías** (CRUD + subcategorías + activo/orden) | Crear, editar, listar, desactivar y ordenar categorías |
| BK-204 | P0 | Hecho | Mantenedor de **productos** (CRUD) | Crear, editar, listar, desactivar productos |
| BK-205 | P0 | Hecho | Subida de imágenes de producto (múltiples + imagen principal) | Imágenes se guardan y se muestran en ficha |
| BK-206 | P1 | Hecho | SEO por entidad: `slug`, `meta_title`, `meta_description` en categorías/productos | Campos editables y reflejados en el HTML |
| BK-207 | P1 | Hecho | Mantenedor de **pedidos** (listar, ver detalle, cambiar estado) | Pedidos visibles y con ciclo de estado |
| BK-208 | P2 | Por hacer | Mantenedor de **usuarios admin** (CRUD) | Crear/editar/eliminar usuarios administradores |
| BK-209 | P2 | Hecho | Control de stock y destacados | Producto muestra stock y filtro de destacados |
| BK-210 | P3 | Por hacer | Ajustes generales editables (nombre, correo, WhatsApp, envío) | Configuración cambia desde el panel |

## 3. Tienda pública / catálogo

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-301 | P0 | Hecho | Página de inicio con destacados y categorías | Productos destacados y acceso a categorías |
| BK-302 | P0 | Hecho | Catálogo general con paginación | Listado paginado de productos activos |
| BK-303 | P0 | Hecho | Listado por categoría (`/categoria/{slug}`) | Productos filtrados por categoría |
| BK-304 | P0 | Hecho | Ficha de producto (`/producto/{slug}`) con galería | Detalle, imágenes, precio y botón comprar |
| BK-305 | P1 | En progreso | Búsqueda de productos | Búsqueda por nombre/descripción (modelo listo; falta UI/ruta) |
| BK-306 | P1 | En progreso | Precio normal vs. oferta y stock visible | Oferta reflejada; "agotado" aún no se muestra en público |
| BK-307 | P3 | Por hacer | Filtros (precio, categoría) y orden | Ordenar/filtrar catálogo |
| BK-308 | P1 | Hecho | Sección "Destacados del mes" (super ventas) en portada | Productos marcados como super venta se muestran tras el hero |

## 4. Carrito y checkout

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-401 | P0 | Hecho | Carrito con sesión (agregar, quitar, actualizar cantidades) | Carrito persistente durante la sesión |
| BK-402 | P0 | Hecho | **Tipo de pago**: pasarela en línea **PagaAquí (BancoEstado)** en CLP | Decisión de negocio documentada |
| BK-403 | P0 | Hecho | Checkout con datos de envío + resumen del pedido | Pedido se registra con ítems y totales |
| BK-404 | P1 | Por hacer | Confirmación por correo al cliente y al vendedor | Correo de confirmación enviado |
| BK-405 | P1 | Por hacer | Integración pasarela **PagaAquí (BancoEstado)** | Pago en línea registrado y validado |
| BK-406 | P3 | Por hacer | Costos de envío por región/monto | Cálculo de envío automático |
| BK-407 | P3 | Por hacer | Soporte multi-moneda (MVP2) | Precios y pago en más de una moneda |

## 5. SEO / SEM

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-501 | P0 | Hecho | URLs amigables y sin IDs (`/producto/{slug}`, `/categoria/{slug}`) | URLs limpias en todo el sitio |
| BK-502 | P0 | Hecho | Metadatos por página/producto/categoría (title, description) | `<title>` y `meta description` únicos por página |
| BK-503 | P1 | Por hacer | `sitemap.xml` dinámico | Sitemap listado por buscadores |
| BK-504 | P1 | Por hacer | `robots.txt` | Rastreo correcto, excluyendo `/admin` |
| BK-505 | P1 | Por hacer | Datos estructurados Schema.org `Product` | JSON-LD válido en ficha de producto |
| BK-506 | P2 | Hecho | Open Graph + Twitter Cards | Vista previa correcta al compartir (OG básico listo) |
| BK-507 | P2 | En progreso | Canonical + redirecciones 301 de slugs antiguos | Evita contenido duplicado (soporte listo; falta cablear) |
| BK-508 | P3 | Por hacer | Integración con Google Analytics / Search Console / píxel de conversión | Métricas de tráfico y conversión |
| BK-509 | P0 | Hecho | Etiqueta Google Ads (gtag.js) `AW-18397361572` en todo el sitio | Tag cargado en el `<head>` de todas las páginas públicas |

## 6. Contenido corporativo y contacto

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-601 | P0 | Hecho | Página de contacto con formulario | Formulario envía correo |
| BK-602 | P1 | Hecho | Página corporativa / "Regalos corporativos" | Contenido institucional y de servicios B2B |
| BK-603 | P2 | Por hacer | Enlace WhatsApp flotante | Chat directo desde el sitio |
| BK-604 | P2 | Por hacer | Páginas legales (términos, privacidad, cambios y devoluciones) | Páginas publicadas |

## 7. Despliegue y puesta en marcha

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-701 | P0 | Hecho | Configurar web root en `public/` (o `.htaccess` raíz) | Sitio sirve desde hosting compartido |
| BK-702 | P0 | Por hacer | Cargar esquema + datos en hosting | BD operativa en producción |
| BK-703 | P1 | Por hacer | HTTPS y dominio definitivo | Sitio accesible por `https://` |
| BK-704 | P1 | Por hacer | Prueba de flujo completo (catálogo → carrito → pedido) | Pedido de punta a punta exitoso |
| BK-705 | P2 | Por hacer | Respaldo de BD y archivos | Respaldo programado |

## 8. Mejoras futuras

| ID | Prioridad | Estado | Tarea |
|----|-----------|--------|-------|
| BK-801 | P3 | Por hacer | Cupones / descuentos |
| BK-802 | P3 | Por hacer | Productos personalizables (texto grabado, fotos) |
| BK-803 | P3 | Por hacer | Valoraciones y comentarios de clientes |
| BK-804 | P3 | Por hacer | Lista de deseos |
| BK-805 | P3 | Por hacer | Multiidioma (ES/EN) |

## 9. Contabilidad y facturación

| ID | Prioridad | Estado | Tarea | Criterio de aceptación |
|----|-----------|--------|-------|------------------------|
| BK-901 | P1 | Por hacer | Ingreso de facturas (documentos fiscales) | Registrar factura de compra/venta con número, fecha, proveedor/cliente |
| BK-902 | P1 | Por hacer | Libro de ventas | Listado de documentos de venta con totales y estado tributario |
| BK-903 | P1 | Por hacer | Costo neto por producto | Campo de costo neto en producto para calcular márgenes |
| BK-904 | P1 | Por hacer | Márgenes sobre el neto | Margen calculado por producto/pedido a partir del costo neto |
| BK-905 | P1 | Por hacer | Impuestos por producto/categoría (IVA, ILA, etc.) | Impuestos configurables y aplicados según tipo de producto |
| BK-906 | P1 | Por hacer | Conexión ventas ↔ facturación | Vincular pedidos/ventas con documentos fiscales y costos |

> Pregunta abierta (define el diseño): ¿las facturas se generan desde los pedidos
> de la tienda (venta = factura), o es un libro de facturación manual que luego se
> concilia con las ventas?

---

### Decisiones cerradas
- **BK-402 / BK-405**: Pago en línea con **PagaAquí (BancoEstado)** en **CLP** (MVP1). Multi-moneda queda para MVP2 (BK-407).
- **BK-103**: País **Chile**, moneda **CLP** (peso chileno).

### Próximos pasos sugeridos (por prioridad)
1. **BK-405** — Integrar pasarela PagaAquí (requiere credenciales/API del comercio).
2. **BK-404** — Correos de confirmación al cliente y al vendedor.
3. **BK-503 / BK-504 / BK-505** — `sitemap.xml`, `robots.txt` y Schema.org `Product`.
4. **BK-704** — Prueba de flujo completo en hosting (requiere entorno PHP/MySQL).
