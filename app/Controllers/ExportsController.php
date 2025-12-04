<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Material;
use App\Models\Agent;

class ExportsController extends Controller
{
    public function exportMaterials()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        $materialModel = new Material();
        $materials = $materialModel->findAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="materiels_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Catégorie', 'Marque', 'Modèle', 'Numéro de série', 'État', 'Date d\'achat', 'Prix', 'Notes']);

        foreach ($materials as $material) {
            fputcsv($output, [
                $material['id'],
                $material['category_name'],
                $material['brand'],
                $material['model'],
                $material['serial_number'],
                $material['status'],
                $material['purchase_date'],
                $material['price'],
                $material['notes']
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportAgents()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        $agentModel = new Agent();
        $agents = $agentModel->findAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="agents_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Département', 'Poste', 'ID Employé']);

        foreach ($agents as $agent) {
            fputcsv($output, [
                $agent['id'],
                $agent['first_name'],
                $agent['last_name'],
                $agent['email'],
                $agent['phone'],
                $agent['department'],
                $agent['position'],
                $agent['employee_id']
            ]);
        }

        fclose($output);
        exit;
    }
}
