<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Assignment;
use App\Models\Agent;
use Exception;

class AssignmentsController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        $page = (int) ($_GET['page'] ?? 1);
        $search = trim($_GET['q'] ?? '');
        $sortColumn = $_GET['sort'] ?? 'assigned_at';
        $sortOrder = $_GET['order'] ?? 'desc';

        if ($page < 1)
            $page = 1;

        $assignmentModel = new Assignment();
        $pagination = $assignmentModel->paginate($page, 10, $search, $sortColumn, $sortOrder);

        $this->view('assignments/index', [
            'assignments' => $pagination['data'],
            'pagination' => $pagination,
            'search' => $search,
            'sortColumn' => $sortColumn,
            'sortOrder' => $sortOrder
        ]);
    }

    public function create()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            $this->redirect('/login');
        }

        $agentModel = new Agent();
        $agents = $agentModel->findAll();

        $this->view('assignments/create', ['agents' => $agents]);
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
            'agent_id' => (int) ($_POST['agent_id'] ?? 0),
            'material_id' => (int) ($_POST['material_id'] ?? 0),
            'assigned_at' => $_POST['assigned_at'] ?? date('Y-m-d'),
            'condition_on_assign' => $_POST['condition_on_assign'] ?? null,
            'note' => $_POST['note'] ?? null
        ];

        if ($data['agent_id'] <= 0 || $data['material_id'] <= 0) {
            $agentModel = new Agent();
            $agents = $agentModel->findAll();
            $this->view('assignments/create', ['error' => 'Veuillez sélectionner un agent et un matériel.', 'agents' => $agents]);
            return;
        }

        try {
            $assignmentModel = new Assignment();
            $assignmentId = $assignmentModel->create($data);

            // Redirect to agent view with download parameter
            $this->redirect('/agents/view?id=' . $data['agent_id'] . '&download_pdf=' . $assignmentId);

        } catch (Exception $e) {
            $agentModel = new Agent();
            $agents = $agentModel->findAll();
            $this->view('assignments/create', ['error' => $e->getMessage(), 'agents' => $agents]);
        }
    }

    public function returnMaterial()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            exit;
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
            exit;
        }

        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);

        try {
            $assignmentModel = new Assignment();
            $result = $assignmentModel->markAsReturned($assignmentId);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'assignment_id' => $assignmentId]);

        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function searchMaterials()
    {
        $query = trim($_GET['q'] ?? '');

        $assignmentModel = new Assignment();
        $results = $assignmentModel->findAvailableMaterials($query);

        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
    }

    public function generatePdf()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            die("Accès refusé.");
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0)
            die("ID invalide");

        $assignmentModel = new Assignment();
        $data = $assignmentModel->findByIdWithDetails($id);

        if (!$data)
            die("Attribution introuvable.");

        $this->renderPdf($data, 'assignment');
    }

    public function generateReturnPdf()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])) {
            die("Accès refusé.");
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0)
            die("ID invalide");

        $assignmentModel = new Assignment();
        $data = $assignmentModel->findByIdWithDetails($id);

        if (!$data)
            die("Attribution introuvable.");

        $this->renderPdf($data, 'return');
    }

    private function renderPdf($data, $type)
    {
        $admin_user_name = $_SESSION['user_name'] ?? 'N/A';
        $logo_path = __DIR__ . '/../../images/logo_crous.png'; // Updated to match header.php logo
        if (!file_exists($logo_path) || !extension_loaded('gd')) {
            // Fallback or error handling
            $logo_base64 = '';
        } else {
            $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $logo_data = file_get_contents($logo_path);
            $logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);
        }

        $title = ($type === 'assignment') ? "Bon d'attribution" : "Bon de retour";
        $filename_prefix = ($type === 'assignment') ? "Remise" : "Retour";

        $crous_text = ($type === 'assignment')
            ? "Vous êtes agent du CROUS de l’académie de Versailles ; dans ce cadre, la DSI vous a remis du matériel nécessaire pour mener à bien vos missions. Il vous est demandé de rendre ce matériel dans un parfait état de fonctionnement et rappelé qu’il s’agit d’un outil de travail dont l’usage doit rester strictement professionnel."
            : "Le gestionnaire de parc reconnaît avoir réceptionné le matériel décrit ci-dessus. Veuillez annoter l'état du matériel en bas de la page";

        $date_label = ($type === 'assignment') ? "Date d'attribution" : "Date de retour";
        $date_value = ($type === 'assignment')
            ? date("d/m/Y", strtotime($data['assigned_at']))
            : ($data['returned_at'] ? date("d/m/Y", strtotime($data['returned_at'])) : 'Date non définie');

        $html = '
        <!doctype html>
        <html lang="fr">
        <head>
        <meta charset="UTF-8">
        <title>' . $title . '</title>
        <style>
            body { font-family: sans-serif; font-size: 13px; color: #374151; }
            .header { margin-bottom: 30px; }
            .header img { width: 100px; height: auto; }
            .header h1 { margin: 0; font-size: 16px; color: #041b4dff; text-align: center; }
            h2 { font-size: 14px; color: #064db0ff; border-bottom: 1px solid #D1D5DB; padding-bottom: 5px; margin-top: 25px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #E5E7EB; padding: 8px; text-align: left; }
            th { background-color: #F9FAFB; font-weight: bold; width: 30%; }
            .notice { 
                margin-top: 25px; 
                padding: 15px; 
                background-color: #F3F4F6; 
                border-left: 3px solid #6B7280; 
                font-size: 14px; 
                line-height: 1.5;
                color: #4B5563;
            }
            .footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 11px; color: #211334ff; }
            .signature-line { border-top: 1px solid #333; margin-top: 50px; }
        </style>
        </head>
        <body>
            <div class="header">
                ' . ($logo_base64 ? '<img src="' . $logo_base64 . '" alt="Logo"><br>' : '') . '
                <h1>' . $title . ' de matériel - Crous de Versailles</h1>
            </div>
            <p>' . $date_label . ' : <strong>' . $date_value . '</strong></p>
        
            <h2>Bénéficiaire</h2>
            <table>
                <tr><th>Nom</th><td>' . e($data['first_name']) . ' ' . e($data['last_name']) . '</td></tr>
                <tr><th>Poste</th><td>' . e($data['position'] ?? '-') . '</td></tr>
                <tr><th>Département</th><td>' . e($data['department'] ?? '-') . '</td></tr>
            </table>
        
            <h2>Matériel</h2>
            <table>
                <tr><th>Type</th><td>' . e($data['category_name']) . '</td></tr>
                <tr><th>Marque / Modèle</th><td>' . e($data['brand']) . ' ' . e($data['model']) . '</td></tr>
                <tr><th>Numéro de série</th><td>' . e($data['serial_number'] ?? '-') . '</td></tr>
                <tr><th>Étiquette (Asset Tag)</th><td>' . e($data['asset_tag'] ?? '-') . '</td></tr>
                ' . ($type === 'assignment' ? '<tr><th>État lors de l\'attribution</th><td>' . e($data['condition_on_assign'] ?? '-') . '</td></tr>' : '') . '
            </table>
        
            <div class="notice">
                <p><strong>Conditions / Remarque :</strong></p>
                <p>' . e($crous_text) . '</p>
            </div>
            
            <table style="border: none; margin-top: 30px;">
                <tr>
                    <td style="border: none; text-align: center;">
                        Signature avec mention "Lu et Approuvée"
                        <div class="signature-line"></div>
                    </td>
                    <td style="border: none; text-align: center;">
                        ' . ($type === 'assignment' ? 'Remis par' : 'Récupéré par') . ' : ' . e($admin_user_name) . '
                        <div class="signature-line"></div>
                    </td>
                </tr>
            </table>
        
            <div class="footer">
                Document généré par ' . e($admin_user_name) . ' le ' . date("d/m/Y à H:i") . ' via l\'application de gestion de parc.
            </div>
        </body>
        </html>';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = strtoupper(
            $filename_prefix . "-"
            . e($data['category_name']) . "-"
            . e($data['last_name']) . "-"
            . e($data['first_name']) . "-"
            . date("Y-m-d")
        );
        $dompdf->stream($filename, ["Attachment" => true]);
    }
}
