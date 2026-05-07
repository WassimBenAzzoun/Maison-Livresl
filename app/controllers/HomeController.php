<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $bibliothequeModel = new Bibliotheque();
        $livreModel = new Livre();

        $this->render('home', [
            'pageTitle' => 'Maison des Livres | Accueil',
            'activePage' => 'home',
            'bibliotheques' => $bibliothequeModel->allWithBookCounts(),
            'livres' => $livreModel->featured(3),
        ]);
    }
}
