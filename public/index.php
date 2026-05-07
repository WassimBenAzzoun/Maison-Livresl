<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require_once APP_PATH . '/core/helpers.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        APP_PATH . '/config/' . $class . '.php',
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

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
