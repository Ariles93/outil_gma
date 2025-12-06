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

        // 5. Données pour le graphique de répartition par catégorie
        $category_stats_query = $pdo->query("
            SELECT c.name, COUNT(m.id) as count 
            FROM materials m 
            JOIN categories c ON m.category_id = c.id 
            WHERE m.deleted_at IS NULL 
            GROUP BY c.name
        ");
        $category_stats = $category_stats_query->fetchAll(PDO::FETCH_ASSOC);

        // 6. Données pour le graphique d'évolution des attributions (12 derniers mois)
        $history_stats_query = $pdo->query("
            SELECT DATE_FORMAT(assigned_at, '%Y-%m') as month, COUNT(*) as count 
            FROM assignments 
            WHERE assigned_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
            GROUP BY month 
            ORDER BY month ASC
        ");
        $history_stats = $history_stats_query->fetchAll(PDO::FETCH_ASSOC);

        // 5. Données pour le graphique de répartition par catégorie
        $category_stats_query = $pdo->query("
            SELECT c.name, COUNT(m.id) as count 
            FROM materials m 
            JOIN categories c ON m.category_id = c.id 
            WHERE m.deleted_at IS NULL 
            GROUP BY c.name
        ");
        $category_stats = $category_stats_query->fetchAll(PDO::FETCH_ASSOC);

        // 6. Données pour le graphique d'évolution (12 derniers mois)
        // Générer les 12 derniers mois
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-$i months"));
        }

        // Attributions par mois
        $assignments_stats = $pdo->query("
            SELECT DATE_FORMAT(assigned_at, '%Y-%m') as month, COUNT(*) as count 
            FROM assignments 
            WHERE assigned_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
            GROUP BY month
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        // Retours par mois
        $returns_stats = $pdo->query("
            SELECT DATE_FORMAT(returned_at, '%Y-%m') as month, COUNT(*) as count 
            FROM assignments 
            WHERE returned_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
            GROUP BY month
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        // Aligner les données avec les mois
        $history_labels = [];
        $history_assignments = [];
        $history_returns = [];

        foreach ($months as $month) {
            $history_labels[] = date('M Y', strtotime($month . '-01')); // Ex: Jan 2024
            $history_assignments[] = $assignments_stats[$month] ?? 0;
            $history_returns[] = $returns_stats[$month] ?? 0;
        }

        $this->view('dashboard/index', [
            'stats' => $stats,
            'total_agents' => $total_agents,
            'recent_assignments' => $recent_assignments,
            'recent_returns' => $recent_returns,
            'category_stats' => $category_stats,
            'history_labels' => $history_labels,
            'history_assignments' => $history_assignments,
            'history_returns' => $history_returns
        ]);
    }
}
