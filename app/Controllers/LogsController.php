<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Log;

class LogsController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 20;

        $logModel = new Log();
        try {
            $result = $logModel->paginate($page, $perPage);
            $logs = $result['data'];
            $totalLogs = $result['total'];
            $totalPages = ceil($totalLogs / $perPage);
        } catch (\Exception $e) {
            $logs = [];
            $totalPages = 0;
            $error = "Impossible de récupérer les logs : " . $e->getMessage();
        }

        $this->view('logs/index', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'error' => $error ?? null
        ]);
    }
}
