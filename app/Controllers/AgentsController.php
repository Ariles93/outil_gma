<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Agent;
use App\Models\Assignment;

class AgentsController extends Controller
{
    public function show()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("Agent introuvable");
        }

        $agentModel = new Agent();
        $agent = $agentModel->findById($id);

        if (!$agent) {
            die("Agent introuvable");
        }

        $assignmentModel = new Assignment();
        $currentAssignments = $assignmentModel->findCurrentByAgent($id);
        $historyAssignments = $assignmentModel->findHistoryByAgent($id);

        $this->view('agents/view', [
            'agent' => $agent,
            'current' => $currentAssignments,
            'history' => $historyAssignments
        ]);
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $page = (int) ($_GET['page'] ?? 1);
        $search = trim($_GET['q'] ?? '');
        if ($page < 1)
            $page = 1;

        $agentModel = new Agent();
        $pagination = $agentModel->paginate($page, 6, $search); // Using 6 per page as in search.php

        // Fetch active materials for each agent to match search.php functionality
        $assignmentModel = new Assignment();
        foreach ($pagination['data'] as &$agent) {
            $agent['materials'] = $assignmentModel->findCurrentByAgent($agent['id']);
        }

        $this->view('agents/index', [
            'agents' => $pagination['data'],
            'pagination' => $pagination,
            'search' => $search
        ]);
    }

    public function create()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/login');
        }

        $this->view('agents/create');
    }

    public function store()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'department' => $_POST['department'] ?? null,
            'position' => $_POST['position'] ?? null,
            'employee_id' => $_POST['employee_id'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ];

        if (empty($data['first_name']) || empty($data['last_name'])) {
            $this->view('agents/create', ['error' => 'Prénom et nom sont obligatoires.', 'agent' => $data]);
            return;
        }

        $agentModel = new Agent();
        $id = $agentModel->create($data);

        $this->redirect('/agents');
    }
    public function edit()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("Agent introuvable");
        }

        $agentModel = new Agent();
        $agent = $agentModel->findById($id);

        if (!$agent) {
            die("Agent introuvable");
        }

        $this->view('agents/edit', ['agent' => $agent]);
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
        if ($id <= 0) {
            die("ID invalide");
        }

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'department' => $_POST['department'] ?? null,
            'position' => $_POST['position'] ?? null,
            'employee_id' => $_POST['employee_id'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ];

        if (empty($data['first_name']) || empty($data['last_name'])) {
            $agentModel = new Agent();
            $agent = $agentModel->findById($id);
            $this->view('agents/edit', ['error' => 'Prénom et nom sont obligatoires.', 'agent' => $agent]);
            return;
        }

        $agentModel = new Agent();
        $agentModel->update($id, $data);

        $this->redirect('agents');
    }
}
