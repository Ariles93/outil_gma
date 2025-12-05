<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;

class CategoriesController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            $this->redirect('login');
        }

        $categoryModel = new Category();
        $categories = $categoryModel->findAll();

        $this->view('categories/index', ['categories' => $categories]);
    }

    public function create()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        $this->view('categories/create');
    }

    public function store()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $this->view('categories/create', ['error' => 'Le nom est obligatoire.']);
            return;
        }

        $categoryModel = new Category();
        $categoryModel->create($name);

        $this->redirect('categories');
    }

    public function edit()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $categoryModel = new Category();
        $category = $categoryModel->findById($id);

        if (!$category) {
            die("Catégorie introuvable");
        }

        $this->view('categories/edit', ['category' => $category]);
    }

    public function update()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $categoryModel = new Category();
            $category = $categoryModel->findById($id);
            $this->view('categories/edit', ['category' => $category, 'error' => 'Le nom est obligatoire.']);
            return;
        }

        $categoryModel = new Category();
        $categoryModel->update($id, $name);

        $this->redirect('categories');
    }

    public function delete()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $categoryModel = new Category();

        if (!$categoryModel->delete($id)) {
            // In a real app, we'd pass an error message. For now, we just redirect.
            // Ideally: $this->redirect('categories?error=used');
        }

        $this->redirect('categories');
    }
}
