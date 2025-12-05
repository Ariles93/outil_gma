<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Config\Database;
use PDO;
use DateTime;

class DashboardController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $pdo = Database::getInstance()->getConnection();

        // 1. Compter les matériels par statut
        $stats_query = $pdo->query("
            SELECT
                (SELECT COUNT(*) FROM materials WHERE deleted_at IS NULL) as total,
                (SELECT COUNT(*) FROM materials WHERE status = 'available' AND deleted_at IS NULL) as available,
                (SELECT COUNT(*) FROM materials WHERE status = 'assigned' AND deleted_at IS NULL) as assigned
        ");
        $stats = $stats_query->fetch();

        // 2. Compter le nombre total d'agents
        $total_agents = $pdo->query("SELECT COUNT(*) FROM agents")->fetchColumn();

        // 3. Récupérer les 5 dernières attributions
        $recent_assignments_query = $pdo->query("
            SELECT a.assigned_at, ag.first_name, ag.last_name, c.name as category_name, m.brand, m.model, a.condition_on_assign
            FROM assignments a
            JOIN agents ag ON a.agent_id = ag.id
            JOIN materials m ON a.material_id = m.id
            JOIN categories c ON m.category_id = c.id
            ORDER BY a.assigned_at DESC, a.id DESC
            LIMIT 5
        ");
        $recent_assignments = $recent_assignments_query->fetchAll();

        // 4. Récupérer les 5 derniers retours
        $recent_returns_query = $pdo->query("
            SELECT a.returned_at, ag.first_name, ag.last_name, c.name as category_name, m.brand, m.model
            FROM assignments a
            JOIN agents ag ON a.agent_id = ag.id
            JOIN materials m ON a.material_id = m.id
            JOIN categories c ON m.category_id = c.id
            WHERE a.returned_at IS NOT NULL
            ORDER BY a.returned_at DESC, a.id DESC
            LIMIT 5
        ");
        $recent_returns = $recent_returns_query->fetchAll();

        $this->view('dashboard/index', [
            'stats' => $stats,
            'total_agents' => $total_agents,
            'recent_assignments' => $recent_assignments,
            'recent_returns' => $recent_returns
        ]);
    }
}
