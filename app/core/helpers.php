<?php

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $default;
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('flash_get')) {
    function flash_get(): ?array
    {
        if (empty($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }
}

if (!function_exists('redirect_page')) {
    function redirect_page(string $page, array $params = []): void
    {
        header('Location: ' . url($page, $params));
        exit;
    }
}

if (!function_exists('require_login_page')) {
    function require_login_page(): void
    {
        if (empty($_SESSION['user'])) {
            flash_set('warning', 'Vous devez être connecté pour accéder à cette page.');
            redirect_page('login');
        }
    }
}

if (!function_exists('require_admin_page')) {
    function require_admin_page(): void
    {
        if (empty($_SESSION['admin'])) {
            flash_set('warning', 'Accès réservé à l\'administrateur.');
            redirect_page('login');
        }
    }
}

if (!function_exists('route_map')) {
    function route_map(): array
    {
        return [
            'home' => 'home.php',
            'books' => 'books.php',
            'book' => 'book.php',
            'borrow' => 'borrow.php',
            'confirmation' => 'confirmation.php',
            'register' => 'register.php',
            'login' => 'login.php',
            'logout' => 'logout.php',
            'profile' => 'profile.php',
            'my-borrowings' => 'my-borrowings.php',
            'admin-login' => 'login.php',
            'admin-logout' => 'admin-logout.php',
            'admin-dashboard' => 'admin-dashboard.php',
            'admin-books' => 'admin-books.php',
            'admin-book-form' => 'admin-book-form.php',
            'admin-book-delete' => 'admin-book-delete.php',
            'admin-borrowings' => 'admin-borrowings.php',
            'admin-users' => 'admin-users.php',
            'admin-user-view' => 'admin-user-view.php',
            'admin-user-action' => 'admin-user-action.php',
            'admin-branches' => 'admin-branches.php',
            'admin-branch-view' => 'admin-branch-view.php',
            'admin-branch-form' => 'admin-branch-form.php',
            'admin-branch-delete' => 'admin-branch-delete.php',
            'admin-statistics' => 'admin-statistics.php',
        ];
    }
}

if (!function_exists('route_file')) {
    function route_file(string $page): string
    {
        return route_map()[$page] ?? route_map()['home'];
    }
}

if (!function_exists('url')) {
    function url(string $page, array $params = []): string
    {
        $query = $params ? '?' . http_build_query($params) : '';
        return route_file($page) . $query;
    }
}

if (!function_exists('format_date_fr')) {
    function format_date_fr(?string $date): string
    {
        if (!$date) {
            return '-';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '-';
        }

        return date('d/m/Y', $timestamp);
    }
}

if (!function_exists('badge_class')) {
    function badge_class(string $status): string
    {
        return match ($status) {
            'confirmed', 'active', 'available', 'returned' => 'badge-success',
            'pending' => 'badge-warning',
            'cancelled', 'inactive', 'unavailable' => 'badge-danger',
            default => 'badge-info',
        };
    }
}

if (!function_exists('status_label')) {
    function status_label(string $status): string
    {
        return match ($status) {
            'confirmed' => 'Confirmé',
            'pending' => 'En attente',
            'cancelled' => 'Annulé',
            'returned' => 'Retourné',
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'available' => 'Disponible',
            'unavailable' => 'Indisponible',
            default => ucfirst($status),
        };
    }
}

if (!function_exists('role_label')) {
    function role_label(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrateur',
            'user' => 'Utilisateur',
            default => ucfirst($role),
        };
    }
}

if (!function_exists('membership_label')) {
    function membership_label(string $type): string
    {
        return match ($type) {
            'monthly' => 'Mensuelle',
            'yearly' => 'Annuelle',
            'none' => 'Sans adhésion',
            default => ucfirst($type),
        };
    }
}
