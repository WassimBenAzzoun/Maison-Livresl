<?php

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


if (!function_exists('require_login_page')) {
    function require_login_page(): void
    {
        if (empty($_SESSION['user'])) {
            flash_set('warning', 'Vous devez être connecté pour accéder à cette page.');
            header('Location: /guest/login.php');
            exit;
        }
    }
}

if (!function_exists('require_admin_page')) {
    function require_admin_page(): void
    {
        if (empty($_SESSION['admin'])) {
            flash_set('warning', 'Accès réservé à l\'administrateur.');
            header('Location: /guest/login.php');
            exit;
        }
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
