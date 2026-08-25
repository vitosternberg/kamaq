<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tax;

class ProductController extends Controller
{
    private string $uploadDir;

    public function __construct()
    {
        $this->uploadDir = BASE_PATH . '/public/uploads/products';
    }

    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/products/index', [
            'pageTitle' => 'Productos',
            'products' => Product::adminList(),
        ], 'admin');
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->view('admin/products/form', [
            'pageTitle' => 'Nuevo producto',
            'product' => null,
            'images' => [],
            'categories' => Category::flatten(Category::treeWithCounts()),
            'taxes' => Tax::active(),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/productos');
        }
        $data = $this->dataFromRequest();
        $data['slug'] = $this->uniqueSlug($data['slug'], null);
        $id = Product::create($data);
        Product::update($id, ['sku' => Product::generateSku($id)]);
        $this->handleImages($id, $_FILES['images'] ?? null);
        flash('success', 'Producto creado.');
        redirect('/admin/productos');
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();
        $product = Product::find($id);
        if (!$product) {
            flash('error', 'Producto no encontrado.');
            redirect('/admin/productos');
        }
        $this->view('admin/products/form', [
            'pageTitle' => 'Editar producto',
            'product' => $product,
            'images' => ProductImage::forProduct($id),
            'categories' => Category::flatten(Category::treeWithCounts()),
            'taxes' => Tax::active(),
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/productos');
        }
        $data = $this->dataFromRequest();
        $data['slug'] = $this->uniqueSlug($data['slug'], $id);
        Product::update($id, $data);
        $this->handleImages($id, $_FILES['images'] ?? null);
        flash('success', 'Producto actualizado.');
        redirect('/admin/productos');
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/productos');
        }
        Product::delete($id);
        flash('success', 'Producto eliminado.');
        redirect('/admin/productos');
    }

    public function deleteImage(int $imageId): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/productos');
        }
        $productId = (int) ($_POST['product_id'] ?? 0);
        ProductImage::delete($imageId);
        flash('success', 'Imagen eliminada.');
        redirect('/admin/productos/editar/' . $productId);
    }

    public function toggleFeatured(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/productos');
        }
        $product = Product::find($id);
        if (!$product) {
            flash('error', 'Producto no encontrado.');
            redirect('/admin/productos');
        }
        $isFeatured = !empty($product['is_featured']);
        Product::update($id, ['is_featured' => $isFeatured ? 0 : 1]);
        flash('success', $isFeatured ? 'Producto quitado de destacados.' : 'Producto destacado.');
        redirect('/admin/productos');
    }

    public function toggleBestseller(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/productos');
        }
        $product = Product::find($id);
        if (!$product) {
            flash('error', 'Producto no encontrado.');
            redirect('/admin/productos');
        }
        $isBestseller = !empty($product['is_bestseller']);
        Product::update($id, ['is_bestseller' => $isBestseller ? 0 : 1]);
        flash('success', $isBestseller ? 'Producto quitado de super ventas.' : 'Producto marcado como super venta.');
        redirect('/admin/productos');
    }

    private function dataFromRequest(): array
    {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $salePrice = trim($_POST['sale_price'] ?? '');
        return [
            'name' => $name,
            'slug' => $slug !== '' ? $slug : slugify($name),
            'category_id' => ((int) ($_POST['category_id'] ?? 0)) ?: null,
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0),
            'sale_price' => $salePrice !== '' ? (float) $salePrice : null,
            'stock' => (int) ($_POST['stock'] ?? 0),
            'cost' => ($_POST['cost'] ?? '') !== '' ? (float) ($_POST['cost'] ?? 0) : null,
            'margin_percent' => ($_POST['margin_percent'] ?? '') !== '' ? (float) ($_POST['margin_percent'] ?? 0) : null,
            'tax_id' => ((int) ($_POST['tax_id'] ?? 0)) ?: null,
            'weight' => ($_POST['weight'] ?? '') !== '' ? (float) ($_POST['weight'] ?? 0) : null,
            'length' => ($_POST['length'] ?? '') !== '' ? (float) ($_POST['length'] ?? 0) : null,
            'width' => ($_POST['width'] ?? '') !== '' ? (float) ($_POST['width'] ?? 0) : null,
            'height' => ($_POST['height'] ?? '') !== '' ? (float) ($_POST['height'] ?? 0) : null,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_bestseller' => isset($_POST['is_bestseller']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
        ];
    }

    private function uniqueSlug(string $slug, ?int $excludeId): string
    {
        $base = slugify($slug);
        $candidate = $base;
        $i = 2;
        while (Product::slugExists($candidate, $excludeId)) {
            $candidate = $base . '-' . $i;
            $i++;
        }
        return $candidate;
    }

    private function handleImages(int $productId, ?array $files): void
    {
        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }

        if ($files && !empty($files['name'][0])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }
                $tmp = $files['tmp_name'][$i];
                $ext = strtolower(pathinfo((string) $files['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    continue;
                }
                $filename = 'p' . $productId . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($tmp, $this->uploadDir . '/' . $filename)) {
                    ProductImage::create([
                        'product_id' => $productId,
                        'filename' => $filename,
                        'sort_order' => $i,
                        'is_primary' => 0,
                    ]);
                }
            }
        }

        // Garantiza que siempre exista una imagen principal
        if (!ProductImage::primary($productId)) {
            $images = ProductImage::forProduct($productId);
            if ($images) {
                ProductImage::update((int) $images[0]['id'], ['is_primary' => 1]);
            }
        }
    }
}
