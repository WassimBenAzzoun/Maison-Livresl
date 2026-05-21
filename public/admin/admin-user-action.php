<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/User.php';

require_admin_page();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$action = $_POST['action'] ?? '';
$userModel = new User();
$target = $id > 0 ? $userModel->find($id) : null;

if ($target && $target->getRole() === 'admin') {
    flash_set('warning', 'Les comptes administrateur ne peuvent pas être modifiés depuis cette page.');
} elseif ($id > 0) {
    if ($action === 'toggle') {
        $userModel->toggleStatus($id);
        flash_set('success', 'Statut utilisateur mis à jour.');
    } elseif ($action === 'delete') {
        $userModel->delete($id);
        flash_set('success', 'Utilisateur supprimé.');
    }
}

header('Location: /admin/admin-users.php');
exit;


