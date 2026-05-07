<?php

class EmpruntController extends Controller
{
    public function borrow(): void
    {
        $this->requireLogin();
        $user = $this->currentUser();
        $userModel = new User();
        $livreModel = new Livre();
        $bibliothequeModel = new Bibliotheque();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $livre = $livreModel->find($id);

        if (!$livre) {
            $this->flash('danger', 'Livre introuvable.');
            $this->redirect('books');
        }

        $sessionUser = $this->currentUser();
        $userRecord = $userModel->findWithMembership((int) $sessionUser['id']);

        if (!$userRecord) {
            $this->flash('danger', 'Utilisateur introuvable.');
            $this->redirect('logout');
        }

        if (!$userRecord->hasActiveMembership()) {
            $this->flash('warning', 'Une adhésion valide est requise avant de pouvoir emprunter.');
            $this->redirect('profile');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $borrowDate = $_POST['borrow_date'] ?? '';
            $returnDate = $_POST['return_date'] ?? '';

            if ($fullName === '' || $email === '' || $phone === '' || $borrowDate === '' || $returnDate === '') {
                $this->flash('warning', 'Veuillez remplir tous les champs.');
            } elseif (strtotime($returnDate) <= strtotime($borrowDate)) {
                $this->flash('warning', 'La date de retour doit être postérieure à la date d\'emprunt.');
            } elseif ($livre->getAvailableExemplaires() <= 0) {
                $this->flash('danger', 'Ce livre n\'est plus disponible.');
            } else {
                $bibliotheque = $livre->getBibliothequeId() ? $bibliothequeModel->find($livre->getBibliothequeId()) : null;
                $empruntModel = new Emprunt();
                $empruntId = $empruntModel->create([
                    'user_id' => $user['id'],
                    'livre_id' => $livre->getId(),
                    'bibliotheque_id' => $livre->getBibliothequeId(),
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'borrow_date' => $borrowDate,
                    'return_date' => $returnDate,
                    'status' => 'pending',
                    'livre_titre' => $livre->getTitre(),
                    'livre_categorie' => $livre->getCategorie(),
                    'bibliotheque_nom' => $bibliotheque ? $bibliotheque->getNom() : '',
                ]);

                $this->flash('success', 'Votre demande d\'emprunt a été enregistrée.');
                $this->redirect('confirmation', ['id' => $empruntId]);
            }
        }

        $prefill = $userRecord;
        $this->render('emprunts/form', [
            'pageTitle' => 'Maison des Livres | Emprunter un livre',
            'activePage' => 'books',
            'livre' => $livre,
            'user' => $prefill,
            'membership' => $userRecord,
        ]);
    }

    public function confirmation(): void
    {
        $this->requireLogin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $emprunt = (new Emprunt())->find($id);

        if (!$emprunt) {
            $this->flash('danger', 'Confirmation introuvable.');
            $this->redirect('my-borrowings');
        }

        $this->render('emprunts/confirmation', [
            'pageTitle' => 'Maison des Livres | Confirmation',
            'activePage' => 'my-borrowings',
            'emprunt' => $emprunt,
        ]);
    }

    public function adminIndex(): void
    {
        $this->requireAdmin();
        $empruntModel = new Emprunt();
        $livreModel = new Livre();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $action = $_POST['action'] ?? '';
            $emprunt = $empruntModel->find($id);

            if ($emprunt) {
                if ($action === 'confirm' && $emprunt->getStatus() === 'pending') {
                    $livre = $livreModel->find((int) $emprunt->getLivreId());
                    if ($livre && $livre->getAvailableExemplaires() > 0) {
                        $empruntModel->updateStatus($id, 'confirmed');
                        $livreModel->decrementAvailability((int) $emprunt->getLivreId());
                        $this->flash('success', 'Emprunt confirmé.');
                    } else {
                        $this->flash('warning', 'Aucun exemplaire disponible pour confirmer cet emprunt.');
                    }
                } elseif ($action === 'cancel' && in_array($emprunt->getStatus(), ['pending', 'confirmed'], true)) {
                    if ($emprunt->getStatus() === 'confirmed' && $emprunt->getLivreId()) {
                        $livreModel->incrementAvailability((int) $emprunt->getLivreId());
                    }
                    $empruntModel->updateStatus($id, 'cancelled');
                    $this->flash('success', 'Emprunt annulé.');
                } elseif ($action === 'returned' && $emprunt->getStatus() === 'confirmed') {
                    $empruntModel->updateStatus($id, 'returned');
                    if ($emprunt->getLivreId()) {
                        $livreModel->incrementAvailability((int) $emprunt->getLivreId());
                    }
                    $this->flash('success', 'Livre marqué comme retourné.');
                } else {
                    $this->flash('info', 'Aucune modification appliquée à cet emprunt.');
                }
            }

            $this->redirect('admin-borrowings');
        }

        $this->render('admin/borrowings', [
            'pageTitle' => 'Maison des Livres | Gestion des emprunts',
            'activePage' => 'admin-borrowings',
            'emprunts' => $empruntModel->allWithRelations(),
        ]);
    }
}
