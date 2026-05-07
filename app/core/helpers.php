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

if (!function_exists('url')) {
    function url(string $page, array $params = []): string
    {
        return 'index.php?' . http_build_query(array_merge(['page' => $page], $params));
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
