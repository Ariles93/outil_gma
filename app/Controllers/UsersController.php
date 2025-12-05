<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/login');
        }

        $userModel = new User();
        $users = $userModel->findAll();

        $this->view('users/index', ['users' => $users]);
    }

    public function create()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }
        $this->view('users/create');
    }

    public function store()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $firstName = trim($_POST['first_name'] ?? '');

        if (empty($email) || empty($password)) {
            $this->view('users/create', ['error' => 'Email et mot de passe obligatoires.']);
            return;
        }

        $userModel = new User();
        // Check if email exists
        if ($userModel->findByEmail($email)) {
            $this->view('users/create', ['error' => 'Cet email est déjà utilisé.']);
            return;
        }

        $userModel->create($email, $password, $role, $firstName);
        $this->redirect('users');
    }

    public function edit()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            die("Utilisateur introuvable");
        }

        $this->view('users/edit', ['user' => $user]);
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
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $firstName = trim($_POST['first_name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email)) {
            $userModel = new User();
            $user = $userModel->findById($id);
            $this->view('users/edit', ['user' => $user, 'error' => 'L\'email est obligatoire.']);
            return;
        }

        $userModel = new User();
        $userModel->update($id, $email, $role, $firstName, $password);

        $this->redirect('users');
    }

    public function delete()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        if (!check_csrf($_POST['csrf'] ?? '')) {
            die('Token CSRF invalide');
        }

        $id = (int) ($_POST['id'] ?? 0);

        // Prevent deleting self
        if ($id == $_SESSION['user_id']) {
            $this->redirect('users'); // Or show error
            return;
        }

        $userModel = new User();
        $userModel->delete($id);

        $this->redirect('users');
    }
}
