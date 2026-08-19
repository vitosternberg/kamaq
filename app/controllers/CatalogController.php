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
        $total = Product::activeCount();
        $totalPages = max(1, (int) ceil($total / $perPage));

        $this->view('products/index', [
            'pageTitle' => 'Catálogo de regalos — KAMAQ',
            'metaDescription' => 'Explora nuestro catálogo de regalos personalizados y mementos para toda ocasión.',
            'products' => Product::paginate($page, $perPage),
            'categories' => Category::roots(),
            'page' => $page,
            'totalPages' => $totalPages,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('')],
                ['label' => 'Catálogo', 'url' => null],
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
