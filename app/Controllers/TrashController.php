<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Material;

class TrashController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $materialModel = new Material();
        $trashedMaterials = $materialModel->findTrashed();

        $this->view('trash/index', ['materials' => $trashedMaterials]);
    }

    public function restore()
    {
        if (!isset($_SESSION['user_id'])) {
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
        $materialModel->restore($id);

        $this->redirect('/trash');
    }
}
