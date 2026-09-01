<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;

class CatalogController extends Controller
{
    public function index(): void
    {
        $perPage = 12;
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $orderMap = [
            'recientes' => 'p.created_at DESC',
            'precio-asc' => 'p.price ASC',
            'precio-desc' => 'p.price DESC',
            'nombre-asc' => 'p.name ASC',
            'nombre-desc' => 'p.name DESC',
        ];
        $orderKey = (string) ($_GET['orden'] ?? 'recientes');
        $orderBy = $orderMap[$orderKey] ?? $orderMap['recientes'];

        $categoryId = (int) ($_GET['categoria'] ?? 0);
        $categoryIds = [];
        if ($categoryId > 0) {
            $categoryIds = array_merge([$categoryId], array_map(fn ($c) => (int) $c['id'], Category::children($categoryId)));
        }

        $total = Product::catalogCount($categoryIds);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $this->view('products/index', [
            'pageTitle' => 'Catálogo de regalos — KAMAQ',
            'metaDescription' => 'Explora nuestro catálogo de regalos personalizados y mementos para toda ocasión.',
            'products' => Product::catalog($categoryIds, $orderBy, $page, $perPage),
            'categories' => Category::roots(),
            'orderKey' => $orderKey,
            'categoryId' => $categoryId,
            'page' => $page,
            'totalPages' => $totalPages,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Catálogo', 'url' => null],
            ],
        ]);
    }

    public function suggest(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim((string) ($_GET['q'] ?? ''));
        if ($q === '') {
            echo json_encode([]);
            return;
        }
        $out = [];
        foreach (Product::search($q, 8) as $p) {
            $taxId = ($p['tax_id'] ?? null) !== null ? (int) $p['tax_id'] : null;
            $price = !empty($p['sale_price']) && (float) $p['sale_price'] > 0 ? (float) $p['sale_price'] : (float) $p['price'];
            $out[] = [
                'name' => $p['name'],
                'slug' => $p['slug'],
                'price' => money(gross_price($price, $taxId)),
                'cover' => $p['cover'] ?? null,
            ];
        }
        echo json_encode($out);
    }

    public function search(): void
    {
        $q = trim($_GET['q'] ?? '');
        $products = $q !== '' ? Product::search($q) : [];

        $this->view('products/search', [
            'pageTitle' => ($q !== '' ? 'Buscar: ' . $q : 'Buscar') . ' — KAMAQ',
            'metaDescription' => 'Busca regalos personalizados y mementos en KAMAQ.',
            'products' => $products,
            'q' => $q,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Búsqueda', 'url' => null],
            ],
        ]);
    }

    public function category(string $slug): void
    {
        $category = Category::findBySlug($slug);
        if (!$category) {
            (new ErrorController())->notFound();
            return;
        }

        $categoryId = (int) $category['id'];
        $children = Category::children($categoryId);
        $productCategoryIds = array_merge([$categoryId], array_map(fn ($c) => (int) $c['id'], $children));

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => url('')],
            ['label' => 'Catálogo', 'url' => url('catalogo')],
        ];
        $ancestors = Category::ancestors($categoryId);
        foreach ($ancestors as $i => $anc) {
            $isLast = ($i === count($ancestors) - 1);
            $breadcrumbs[] = ['label' => $anc['name'], 'url' => $isLast ? null : url('categoria/' . $anc['slug'])];
        }

        $this->view('products/category', [
            'pageTitle' => ($category['meta_title'] ?: $category['name']) . ' — KAMAQ',
            'metaDescription' => $category['meta_description'] ?: $category['description'] ?: ('Productos de ' . $category['name']),
            'category' => $category,
            'children' => $children,
            'products' => Product::byCategories($productCategoryIds),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            (new ErrorController())->notFound();
            return;
        }

        $category = $product['category_id'] ? Category::find((int) $product['category_id']) : null;
        $related = $category ? Product::related((int) $category['id'], (int) $product['id']) : [];

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => url('')],
            ['label' => 'Catálogo', 'url' => url('catalogo')],
        ];
        if ($category) {
            foreach (Category::ancestors((int) $category['id']) as $anc) {
                $breadcrumbs[] = ['label' => $anc['name'], 'url' => url('categoria/' . $anc['slug'])];
            }
        }
        $breadcrumbs[] = ['label' => $product['name'], 'url' => null];

        $this->view('products/show', [
            'pageTitle' => ($product['meta_title'] ?: $product['name']) . ' — KAMAQ',
            'metaDescription' => $product['meta_description'] ?: $product['short_description'],
            'product' => $product,
            'images' => ProductImage::forProduct((int) $product['id']),
            'category' => $category,
            'related' => $related,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
