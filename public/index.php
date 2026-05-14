<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require_once APP_PATH . '/core/helpers.php';
require_once APP_PATH . '/config/Database.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/Bibliotheque.php';
require_once APP_PATH . '/models/Livre.php';
require_once APP_PATH . '/models/Emprunt.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/controllers/HomeController.php';
require_once APP_PATH . '/controllers/LivreController.php';
require_once APP_PATH . '/controllers/EmpruntController.php';
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/UserController.php';
require_once APP_PATH . '/controllers/AdminController.php';

$page = $_GET['page'] ?? 'home';

$homeController = new HomeController();
$livreController = new LivreController();
$empruntController = new EmpruntController();
$authController = new AuthController();
$userController = new UserController();
$adminController = new AdminController();

switch ($page) {
    case 'home':
        $homeController->index();
        break;
    case 'books':
        $livreController->index();
        break;
    case 'book':
        $livreController->show();
        break;
    case 'borrow':
        $empruntController->borrow();
        break;
    case 'confirmation':
        $empruntController->confirmation();
        break;
    case 'register':
        $authController->register();
        break;
    case 'login':
        $authController->login();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'profile':
        $userController->profile();
        break;
    case 'my-borrowings':
        $userController->borrowings();
        break;
    case 'admin-login':
        header('Location: index.php?page=login');
        exit;
        break;
    case 'admin-logout':
        $adminController->logout();
        break;
    case 'admin-dashboard':
        $adminController->dashboard();
        break;
    case 'admin-books':
        $livreController->adminIndex();
        break;
    case 'admin-book-form':
        $livreController->adminForm();
        break;
    case 'admin-book-delete':
        $livreController->adminDelete();
        break;
    case 'admin-borrowings':
        $empruntController->adminIndex();
        break;
    case 'admin-users':
        $adminController->users();
        break;
    case 'admin-user-view':
        $adminController->userView();
        break;
    case 'admin-user-action':
        $adminController->userAction();
        break;
    case 'admin-branches':
        $adminController->branches();
        break;
    case 'admin-branch-view':
        $adminController->branchView();
        break;
    case 'admin-branch-form':
        $adminController->branchForm();
        break;
    case 'admin-branch-delete':
        $adminController->branchDelete();
        break;
    case 'admin-statistics':
        $adminController->statistics();
        break;
    default:
        $homeController->index();
        break;
}
