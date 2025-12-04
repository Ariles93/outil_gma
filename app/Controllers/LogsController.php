<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Log;

class LogsController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/login');
        }

        $logModel = new Log();
        try {
            $logs = $logModel->findAll();
        } catch (\Exception $e) {
            // If table doesn't exist or other error, handle gracefully
            $logs = [];
            $error = "Impossible de récupérer les logs : " . $e->getMessage();
        }

        $this->view('logs/index', ['logs' => $logs, 'error' => $error ?? null]);
    }
}
