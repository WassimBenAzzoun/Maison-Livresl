<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';

unset($_SESSION['user'], $_SESSION['admin']);
flash_set('success', 'Vous êtes déconnecté.');
header('Location: /guest/home.php');
exit;


