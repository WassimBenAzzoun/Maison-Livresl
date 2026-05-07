<?php

class UserController extends Controller
{
    public function profile(): void
    {
        $this->requireLogin();
        $userModel = new User();
        $sessionUser = $this->currentUser();
        $user = $userModel->findWithMembership((int) $sessionUser['id']);

        if (!$user) {
            $this->flash('danger', 'Utilisateur introuvable.');
            $this->redirect('logout');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $password = $_POST['password'] ?? '';
            $existing = $userModel->findByEmail($email);

            if ($fullName === '' || $email === '' || $phone === '') {
                $this->flash('warning', 'Les champs essentiels sont obligatoires.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('warning', 'Adresse email invalide.');
            } elseif ($existing && $existing->getId() !== $user->getId()) {
                $this->flash('warning', 'Cet email est déjà utilisé par un autre compte.');
            } else {
                $userModel->updateProfile((int) $user->getId(), [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'password' => $password ? password_hash($password, PASSWORD_DEFAULT) : '',
                ]);

                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'status' => $user->getStatus(),
                ];

                $this->flash('success', 'Profil mis à jour.');
                $this->redirect('profile');
            }
        }

        $this->render('user/profile', [
            'pageTitle' => 'Mon profil',
            'activePage' => 'profile',
            'user' => $user,
        ]);
    }

    public function borrowings(): void
    {
        $this->requireLogin();
        $sessionUser = $this->currentUser();
        $borrowings = (new Emprunt())->byUser((int) $sessionUser['id']);

        $this->render('user/borrowings', [
            'pageTitle' => 'Mes emprunts',
            'activePage' => 'my-borrowings',
            'borrowings' => $borrowings,
        ]);
    }
}
