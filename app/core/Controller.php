<?php

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $pageTitle = $data['pageTitle'] ?? 'Maison des Livres';
        $activePage = $data['activePage'] ?? '';
        extract($data);

        require APP_PATH . '/views/layout/header.php';
        require APP_PATH . '/views/' . $view . '.php';
        require APP_PATH . '/views/layout/footer.php';
    }

    protected function redirect(string $page, array $params = []): void
    {
        header('Location: ' . url($page, $params));
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    protected function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            $this->flash('warning', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect('login');
        }
    }

    protected function requireAdmin(): void
    {
        if (empty($_SESSION['admin'])) {
            $this->flash('warning', 'Accès réservé à l\'administrateur.');
            $this->redirect('login');
        }
    }

    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function currentAdmin(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }
}
