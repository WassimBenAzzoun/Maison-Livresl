<?php

class AuthController extends Controller
{
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';

            $userModel = new User();

            if ($fullName === '' || $email === '' || $phone === '' || $password === '') {
                $this->flash('warning', 'Tous les champs obligatoires doivent être remplis.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('warning', 'Adresse email invalide.');
            } elseif ($password !== $confirm) {
                $this->flash('warning', 'Les mots de passe ne correspondent pas.');
            } elseif ($userModel->findByEmail($email)) {
                $this->flash('warning', 'Cet email est déjà utilisé.');
            } else {
                $userModel->create([
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'address' => $address,
                    'status' => 'active',
                    'role' => 'user',
                ]);

                $this->flash('success', 'Compte créé avec succès. Vous pouvez vous connecter.');
                $this->redirect('login');
            }
        }

        $this->render('auth/register', [
            'pageTitle' => 'Maison des Livres | Inscription',
            'activePage' => 'register',
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = (new User())->authenticate($email, $password);

            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'full_name' => $user->getFullName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'address' => $user->getAddress(),
                    'status' => $user->getStatus(),
                    'role' => $user->getRole(),
                ];

                if ($user->getRole() === 'admin') {
                    $_SESSION['admin'] = $_SESSION['user'];
                }

                session_regenerate_id(true);
                $this->flash('success', 'Connexion réussie.');
                $this->redirect($user->getRole() === 'admin' ? 'admin-dashboard' : 'profile');
            }

            $this->flash('danger', 'Identifiants invalides ou compte inactif.');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Maison des Livres | Connexion',
            'activePage' => 'login',
        ]);
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['admin']);
        $this->flash('success', 'Vous êtes déconnecté.');
        $this->redirect('home');
    }
}
