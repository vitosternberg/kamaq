<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/categories/index', [
            'pageTitle' => 'Categorías',
            'categories' => Category::flatten(Category::treeWithCounts()),
        ], 'admin');
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->view('admin/categories/form', [
            'pageTitle' => 'Nueva categoría',
            'category' => null,
            'parents' => Category::flatten(Category::treeWithCounts()),
            'excludeIds' => [],
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/categorias');
        }
        $data = $this->dataFromRequest();
        $data['slug'] = $this->uniqueSlug($data['slug'], null);
        Category::create($data);
        flash('success', 'Categoría creada.');
        redirect('/admin/categorias');
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();
        $category = Category::find($id);
        if (!$category) {
            flash('error', 'Categoría no encontrada.');
            redirect('/admin/categorias');
        }
        $this->view('admin/categories/form', [
            'pageTitle' => 'Editar categoría',
            'category' => $category,
            'parents' => Category::flatten(Category::treeWithCounts()),
            'excludeIds' => Category::descendantIds($id),
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/categorias');
        }
        $data = $this->dataFromRequest();
        if ($data['parent_id'] && in_array((int) $data['parent_id'], Category::descendantIds($id), true)) {
            flash('error', 'No puedes asignar esta categoría como hija de sí misma ni de una de sus subcategorías.');
            redirect('/admin/categorias/editar/' . $id);
        }
        $data['slug'] = $this->uniqueSlug($data['slug'], $id);
        Category::update($id, $data);
        flash('success', 'Categoría actualizada.');
        redirect('/admin/categorias');
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/categorias');
        }
        Category::delete($id);
        flash('success', 'Categoría eliminada.');
        redirect('/admin/categorias');
    }

    private function dataFromRequest(): array
    {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        return [
            'name' => $name,
            'slug' => $slug !== '' ? $slug : slugify($name),
            'parent_id' => ((int) ($_POST['parent_id'] ?? 0)) ?: null,
            'description' => trim($_POST['description'] ?? ''),
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function uniqueSlug(string $slug, ?int $excludeId): string
    {
        $base = slugify($slug);
        $candidate = $base;
        $i = 2;
        while (Category::slugExists($candidate, $excludeId)) {
            $candidate = $base . '-' . $i;
            $i++;
        }
        return $candidate;
    }
}
