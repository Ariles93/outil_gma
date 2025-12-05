<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $this->view('auth/login');
    }

    public function authenticate()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $error = '';

        if (empty($email) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['user_role'] = $user['role'];
                $this->redirect('');
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        }

        $this->view('auth/login', ['error' => $error, 'email' => $email]);
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('login');
    }

    public function forgotPassword()
    {
        $this->view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            $this->view('auth/forgot_password', ['error' => 'Veuillez entrer votre email.']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $userModel->setResetToken($email, $token);

            // Simulate email sending
            $resetLink = url("reset-password?token=$token");
            $this->view('auth/forgot_password', ['success' => "Un lien de réinitialisation a été envoyé (simulé): <a href='$resetLink'>$resetLink</a>"]);
        } else {
            // Don't reveal if user exists
            $this->view('auth/forgot_password', ['success' => "Si cet email existe, un lien de réinitialisation a été envoyé."]);
        }
    }

    public function resetPassword()
    {
        $token = $_GET['token'] ?? '';
        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            die("Lien invalide ou expiré.");
        }

        $this->view('auth/reset_password', ['token' => $token]);
    }

    public function updatePassword()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password) || $password !== $confirmPassword) {
            $this->view('auth/reset_password', ['token' => $token, 'error' => 'Les mots de passe ne correspondent pas ou sont vides.']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            die("Lien invalide ou expiré.");
        }

        $userModel->updatePasswordByToken($token, $password);
        $this->redirect('login');
    }
}
