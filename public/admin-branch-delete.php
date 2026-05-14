<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Bibliotheque.php';

require_admin_page();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    (new Bibliotheque())->delete($id);
    flash_set('success', 'Bibliothèque supprimée.');
}

redirect_page('admin-branches');
