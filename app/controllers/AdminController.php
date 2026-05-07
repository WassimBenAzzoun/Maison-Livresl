<?php

class AdminController extends Controller
{
    public function login(): void
    {
        $this->redirect('login');
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['admin']);
        $this->flash('success', 'Administrateur déconnecté.');
        $this->redirect('home');
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $livreModel = new Livre();
        $empruntModel = new Emprunt();
        $userModel = new User();
        $bibliothequeModel = new Bibliotheque();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Maison des Livres | Aperçu de gestion',
            'activePage' => 'admin-dashboard',
            'stats' => [
                'total_books' => $livreModel->countTotal(),
                'available_books' => $livreModel->countAvailable(),
                'total_borrowings' => $empruntModel->countTotal(),
                'pending_borrowings' => $empruntModel->countByStatus('pending'),
                'confirmed_borrowings' => $empruntModel->countByStatus('confirmed'),
                'returned_borrowings' => $empruntModel->countByStatus('returned'),
                'total_users' => $userModel->countTotal(),
                'total_branches' => count($bibliothequeModel->all()),
            ],
            'latestBorrowings' => $empruntModel->latest(5),
        ]);
    }

    public function users(): void
    {
        $this->requireAdmin();
        $this->render('admin/users', [
            'pageTitle' => 'Maison des Livres | Utilisateurs',
            'activePage' => 'admin-users',
            'users' => (new User())->all(),
        ]);
    }

    public function userView(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $userModel = new User();
        $user = $userModel->findWithMembership($id);

        if (!$user) {
            $this->flash('danger', 'Utilisateur introuvable.');
            $this->redirect('admin-users');
        }

        $empruntModel = new Emprunt();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'membership_save') {
            $membershipType = $_POST['membership_type'] ?? 'none';
            $membershipPaidAt = trim($_POST['membership_paid_at'] ?? '');
            $membershipExpiresAt = trim($_POST['membership_expires_at'] ?? '');
            $membershipBranchId = (int) ($_POST['membership_branch_id'] ?? 0);

            if (!in_array($membershipType, ['none', 'monthly', 'yearly'], true)) {
                $this->flash('warning', 'Type d\'adhésion invalide.');
            } else {
                if ($membershipType !== 'none') {
                    $startDate = $membershipPaidAt !== '' ? $membershipPaidAt : date('Y-m-d');
                    $start = new DateTime($startDate);
                    $end = clone $start;
                    $end->modify($membershipType === 'monthly' ? '+1 month' : '+1 year');
                    $membershipPaidAt = $start->format('Y-m-d');
                    $membershipExpiresAt = $end->format('Y-m-d');
                } else {
                    $membershipPaidAt = null;
                    $membershipExpiresAt = null;
                }

                $userModel->updateMembership($id, [
                    'membership_type' => $membershipType,
                    'membership_paid_at' => $membershipPaidAt,
                    'membership_expires_at' => $membershipExpiresAt,
                    'membership_branch_id' => $membershipBranchId > 0 ? $membershipBranchId : null,
                ]);

                $this->flash('success', 'Adhésion mise à jour.');
            }

            $this->redirect('admin-user-view', ['id' => $id]);
        }

        $this->render('admin/user_view', [
            'pageTitle' => 'Maison des Livres | Fiche utilisateur',
            'activePage' => 'admin-users',
            'user' => $user,
            'currentBorrowings' => $empruntModel->currentByUser((int) $user->getId()),
            'previousBorrowings' => $empruntModel->previousByUser((int) $user->getId()),
        ]);
    }

    public function branchView(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $model = new Bibliotheque();
        $branch = $model->find($id);

        if (!$branch) {
            $this->flash('danger', 'Point de service introuvable.');
            $this->redirect('admin-branches');
        }

        $this->render('admin/branch_view', [
            'pageTitle' => 'Maison des Livres | Fiche point de service',
            'activePage' => 'admin-branches',
            'branch' => $branch,
            'books' => $model->booksById($id),
            'currentBorrowings' => $model->currentBorrowingsById($id),
        ]);
    }

    public function userAction(): void
    {
        $this->requireAdmin();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $action = $_POST['action'] ?? '';
        $userModel = new User();
        $target = $id > 0 ? $userModel->find($id) : null;

        if ($target && $target->getRole() === 'admin') {
            $this->flash('warning', 'Les comptes administrateur ne peuvent pas être modifiés depuis cette page.');
        } elseif ($id > 0) {
            if ($action === 'toggle') {
                $userModel->toggleStatus($id);
                $this->flash('success', 'Statut utilisateur mis à jour.');
            } elseif ($action === 'delete') {
                $userModel->delete($id);
                $this->flash('success', 'Utilisateur supprimé.');
            }
        }

        $this->redirect('admin-users');
    }

    public function branches(): void
    {
        $this->requireAdmin();
        $this->render('admin/branches', [
            'pageTitle' => 'Maison des Livres | Points de service',
            'activePage' => 'admin-branches',
            'branches' => (new Bibliotheque())->all(),
        ]);
    }

    public function branchForm(): void
    {
        $this->requireAdmin();
        $model = new Bibliotheque();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id > 0) {
            $branch = $model->find($id);
            if (!$branch) {
                $this->flash('danger', 'Bibliothèque introuvable.');
                $this->redirect('admin-branches');
            }
        } else {
            $branch = new Bibliotheque();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => trim($_POST['nom'] ?? ''),
                'adresse' => trim($_POST['adresse'] ?? ''),
                'ville' => trim($_POST['ville'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'latitude' => (float) ($_POST['latitude'] ?? 0),
                'longitude' => (float) ($_POST['longitude'] ?? 0),
            ];

            if ($data['nom'] === '' || $data['ville'] === '') {
                $this->flash('warning', 'Le nom et la ville sont obligatoires.');
            } elseif ($id > 0) {
                $model->update($id, $data);
                $this->flash('success', 'Bibliothèque mise à jour.');
            } else {
                $model->create($data);
                $this->flash('success', 'Bibliothèque ajoutée.');
            }

            $this->redirect('admin-branches');
        }

        $this->render('admin/branch_form', [
            'pageTitle' => $id ? 'Maison des Livres | Modifier un point de service' : 'Maison des Livres | Ajouter un point de service',
            'activePage' => 'admin-branches',
            'branch' => $branch,
        ]);
    }

    public function branchDelete(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id > 0) {
            (new Bibliotheque())->delete($id);
            $this->flash('success', 'Bibliothèque supprimée.');
        }
        $this->redirect('admin-branches');
    }

    public function statistics(): void
    {
        $this->requireAdmin();
        $livreModel = new Livre();
        $empruntModel = new Emprunt();
        $userModel = new User();

        $stats = [
            'total_books' => $livreModel->countTotal(),
            'available_books' => $livreModel->countAvailable(),
            'total_borrowings' => $empruntModel->countTotal(),
            'pending_borrowings' => $empruntModel->countByStatus('pending'),
            'confirmed_borrowings' => $empruntModel->countByStatus('confirmed'),
            'returned_borrowings' => $empruntModel->countByStatus('returned'),
            'total_users' => $userModel->countTotal(),
            'by_category' => $empruntModel->countByCategory(),
            'by_branch' => $empruntModel->countByBranch(),
        ];

        $this->render('admin/statistics', [
            'pageTitle' => 'Maison des Livres | Statistiques',
            'activePage' => 'admin-statistics',
            'stats' => $stats,
        ]);
    }
}
