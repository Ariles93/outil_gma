<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Material;
use App\Models\Log;
use PDO;

class MaterialsController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $page = (int) ($_GET['page'] ?? 1);
        $search = trim($_GET['q'] ?? '');
        $sortColumn = $_GET['sort'] ?? 'id';
        $sortOrder = $_GET['order'] ?? 'desc';

        if ($page < 1)
            $page = 1;

        $materialModel = new Material();
        $pagination = $materialModel->paginate($page, 10, $search, $sortColumn, $sortOrder);

        $this->view('materials/index', [
            'materials' => $pagination['data'],
            'pagination' => $pagination,
            'search' => $search,
            'sortColumn' => $sortColumn,
            'sortOrder' => $sortOrder
        ]);
    }

    public function show()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("Matériel introuvable");
        }

        $materialModel = new Material();
        $material = $materialModel->findById($id);

        if (!$material) {
            die("Matériel introuvable");
        }

        $history = $materialModel->getAssignmentHistory($id);
        $currentAssignment = $materialModel->getCurrentAssignment($id);

        // Merge current assignment details into material array if exists, for backward compatibility or ease of use in view
        if ($currentAssignment) {
            $material = array_merge($material, $currentAssignment);
        }

        $this->view('materials/view', [
            'material' => $material,
            'history' => $history
        ]);
    }

    public function create()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            $this->redirect('/login');
        }

        // Fetch categories for the dropdown
        $pdo = \App\Config\Database::getInstance()->getConnection();
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

        $this->view('materials/create', ['categories' => $categories]);
    }

    public function store()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            $this->redirect('/login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $data = [
            'category_id' => $_POST['category_id'] ?? null,
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'inventory_number' => trim($_POST['inventory_number'] ?? ''),
            'purchase_date' => !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null,
            'warranty_expiry' => !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null,
            'cost' => $_POST['cost'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ];

        if (empty($data['brand']) || empty($data['model'])) {
            // Fetch categories again for the view
            $pdo = \App\Config\Database::getInstance()->getConnection();
            $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
            $this->view('materials/create', ['error' => 'Marque et Modèle sont obligatoires.', 'material' => $data, 'categories' => $categories]);
            return;
        }

        $materialModel = new Material();
        $newId = $materialModel->create($data);

        $logModel = new Log();
        $logModel->create($_SESSION['user_id'], 'create_material', "Ajout du matériel ID $newId : " . $data['brand'] . " " . $data['model']);

        $_SESSION['success_message'] = "Matériel ajouté avec succès.";

        $this->redirect('/materials');
    }
    public function edit()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            $this->redirect('/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("Matériel introuvable");
        }

        $materialModel = new Material();
        $material = $materialModel->findById($id);

        if (!$material) {
            die("Matériel introuvable");
        }

        // Fetch categories for the dropdown
        $pdo = \App\Config\Database::getInstance()->getConnection();
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

        $this->view('materials/edit', ['material' => $material, 'categories' => $categories]);
    }

    public function update()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            $this->redirect('/login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("ID invalide");
        }

        $data = [
            'category_id' => $_POST['category_id'] ?? null,
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'inventory_number' => trim($_POST['inventory_number'] ?? ''),
            'purchase_date' => !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null,
            'warranty_expiry' => !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null,
            'cost' => $_POST['cost'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ];

        if (empty($data['brand']) || empty($data['model'])) {
            // Fetch categories again for the view
            $pdo = \App\Config\Database::getInstance()->getConnection();
            $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
            $materialModel = new Material();
            $material = $materialModel->findById($id);
            $this->view('materials/edit', ['error' => 'Marque et Modèle sont obligatoires.', 'material' => $material, 'categories' => $categories]);
            return;
        }

        $materialModel = new Material();
        $materialModel->update($id, $data);

        $logModel = new Log();
        $logModel->create($_SESSION['user_id'], 'update_material', "Modification du matériel ID $id");

        $_SESSION['success_message'] = "Matériel modifié avec succès.";

        $this->redirect('/materials');
    }
    public function delete()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            die("ID invalide");
        }

        $materialModel = new Material();
        $material = $materialModel->findById($id);

        if ($material && $material['status'] === 'assigned') {
            // Flash error message
            $_SESSION['error_message'] = "Impossible de supprimer un matériel attribué.";
            $this->redirect('/materials');
            return;
        }

        $materialModel->delete($id);

        $logModel = new Log();
        $logModel->create($_SESSION['user_id'], 'delete_material', "Suppression (corbeille) du matériel ID $id");

        $_SESSION['success_message'] = "Le matériel a bien été mis à la corbeille.";

        $this->redirect('/materials');
    }

    public function apiSearch()
    {
        $q = trim($_GET['q'] ?? '');
        $materialModel = new Material();
        $results = $materialModel->search($q);

        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
        exit;
    }
}
