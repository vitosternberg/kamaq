<?php

declare(strict_types=1);

use App\Core\Router;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = BASE_PATH . '/app/' . $relative . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

require BASE_PATH . '/app/core/functions.php';

session_start();

$router = new Router();

// --- Público ---
$router->get('', 'App\Controllers\HomeController@index');
$router->get('catalogo', 'App\Controllers\CatalogController@index');
$router->get('categoria/{slug}', 'App\Controllers\CatalogController@category');
$router->get('producto/{slug}', 'App\Controllers\CatalogController@show');
$router->get('carrito', 'App\Controllers\CartController@index');
$router->post('carrito/agregar', 'App\Controllers\CartController@add');
$router->post('carrito/actualizar', 'App\Controllers\CartController@update');
$router->post('carrito/quitar', 'App\Controllers\CartController@remove');
$router->get('checkout', 'App\Controllers\CheckoutController@index');
$router->post('checkout', 'App\Controllers\CheckoutController@store');
$router->get('checkout/gracias', 'App\Controllers\CheckoutController@thanks');
$router->get('contacto', 'App\Controllers\ContactController@index');
$router->post('contacto', 'App\Controllers\ContactController@store');
$router->get('corporativo', 'App\Controllers\CorporateController@index');

// --- Administración ---
$router->get('admin/login', 'App\Controllers\Admin\AuthController@showLogin');
$router->post('admin/login', 'App\Controllers\Admin\AuthController@login');
$router->get('admin/logout', 'App\Controllers\Admin\AuthController@logout');
$router->get('admin', 'App\Controllers\Admin\DashboardController@index');
$router->get('admin/categorias', 'App\Controllers\Admin\CategoryController@index');
$router->get('admin/categorias/crear', 'App\Controllers\Admin\CategoryController@create');
$router->post('admin/categorias/guardar', 'App\Controllers\Admin\CategoryController@store');
$router->get('admin/categorias/editar/{id}', 'App\Controllers\Admin\CategoryController@edit');
$router->post('admin/categorias/actualizar/{id}', 'App\Controllers\Admin\CategoryController@update');
$router->post('admin/categorias/eliminar/{id}', 'App\Controllers\Admin\CategoryController@delete');
$router->get('admin/productos', 'App\Controllers\Admin\ProductController@index');
$router->get('admin/productos/crear', 'App\Controllers\Admin\ProductController@create');
$router->post('admin/productos/guardar', 'App\Controllers\Admin\ProductController@store');
$router->get('admin/productos/editar/{id}', 'App\Controllers\Admin\ProductController@edit');
$router->post('admin/productos/actualizar/{id}', 'App\Controllers\Admin\ProductController@update');
$router->post('admin/productos/eliminar/{id}', 'App\Controllers\Admin\ProductController@delete');
$router->post('admin/productos/imagen/eliminar/{id}', 'App\Controllers\Admin\ProductController@deleteImage');
$router->get('admin/pedidos', 'App\Controllers\Admin\OrderController@index');
$router->get('admin/pedidos/{id}', 'App\Controllers\Admin\OrderController@show');
$router->post('admin/pedidos/estado/{id}', 'App\Controllers\Admin\OrderController@updateStatus');

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
