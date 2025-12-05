<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Material;
use App\Models\Log;

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

        $logModel = new Log();
        $logModel->create($_SESSION['user_id'], 'restore_material', "Restauration du matériel ID $id");

        $_SESSION['success_message'] = "Matériel restauré avec succès.";

        $this->redirect('/trash');
    }
}
