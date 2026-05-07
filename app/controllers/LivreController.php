<?php

class LivreController extends Controller
{
    public function index(): void
    {
        $livreModel = new Livre();
        $bibliothequeModel = new Bibliotheque();
        $selectedBranchId = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0;
        $this->render('livres/index', [
            'pageTitle' => 'Maison des Livres | Catalogue',
            'activePage' => 'books',
            'livres' => $livreModel->all(),
            'branches' => $bibliothequeModel->all(),
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $livreModel = new Livre();
        $livre = $livreModel->find($id);

        if (!$livre) {
            $this->flash('danger', 'Livre introuvable.');
            $this->redirect('books');
        }

        $bibliotheque = $livre->getBibliothequeId() ? (new Bibliotheque())->find($livre->getBibliothequeId()) : null;

        $this->render('livres/show', [
            'pageTitle' => 'Maison des Livres | ' . $livre->getTitre(),
            'activePage' => 'books',
            'livre' => $livre,
            'bibliotheque' => $bibliotheque,
        ]);
    }

    public function adminIndex(): void
    {
        $this->requireAdmin();
        $this->render('admin/books', [
            'pageTitle' => 'Maison des Livres | Gestion des livres',
            'activePage' => 'admin-books',
            'livres' => (new Livre())->all(),
        ]);
    }

    public function adminForm(): void
    {
        $this->requireAdmin();
        $model = new Livre();
        $bibliotheques = (new Bibliotheque())->all();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id > 0) {
            $livre = $model->find($id);
            if (!$livre) {
                $this->flash('danger', 'Livre introuvable.');
                $this->redirect('admin-books');
            }
        } else {
            $livre = new Livre();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'bibliotheque_id' => (int) ($_POST['bibliotheque_id'] ?? 0),
                'titre' => trim($_POST['titre'] ?? ''),
                'auteur' => trim($_POST['auteur'] ?? ''),
                'categorie' => trim($_POST['categorie'] ?? ''),
                'annee_publication' => (int) ($_POST['annee_publication'] ?? 0),
                'description' => trim($_POST['description'] ?? ''),
                'couverture' => trim($_POST['couverture'] ?? ''),
                'total_exemplaires' => (int) ($_POST['total_exemplaires'] ?? 0),
                'available_exemplaires' => (int) ($_POST['available_exemplaires'] ?? 0),
            ];

            if ($data['titre'] === '' || $data['auteur'] === '' || $data['categorie'] === '') {
                $this->flash('warning', 'Veuillez remplir les champs obligatoires.');
            } elseif ($data['total_exemplaires'] < 1) {
                $this->flash('warning', 'Le nombre total d\'exemplaires doit être supérieur à zéro.');
            } elseif ($data['available_exemplaires'] < 0 || $data['available_exemplaires'] > $data['total_exemplaires']) {
                $this->flash('warning', 'Les exemplaires disponibles doivent être compris entre 0 et le total.');
            } elseif ($id > 0) {
                $model->update($id, $data);
                $this->flash('success', 'Livre mis à jour avec succès.');
            } else {
                $model->create($data);
                $this->flash('success', 'Livre ajouté avec succès.');
            }

            $this->redirect('admin-books');
        }

        $this->render('admin/book_form', [
            'pageTitle' => $id ? 'Maison des Livres | Modifier un livre' : 'Maison des Livres | Ajouter un livre',
            'activePage' => 'admin-books',
            'livre' => $livre,
            'bibliotheques' => $bibliotheques,
        ]);
    }

    public function adminDelete(): void
    {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            (new Livre())->delete($id);
            $this->flash('success', 'Livre supprimé.');
        }

        $this->redirect('admin-books');
    }
}
