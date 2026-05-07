# Library Management PHP OOP

Application web de gestion de bibliothèque réalisée en PHP orienté objet, MySQL/PDO, HTML/CSS et JavaScript vanilla.

## Technologies

- PHP OOP
- MySQL avec PDO
- HTML5 / CSS3
- Vanilla JavaScript
- Leaflet.js + OpenStreetMap pour la carte

La gestion des comptes repose sur une seule table `users` avec un champ `role` (`user` ou `admin`).

## Fonctionnalités

- Accueil avec présentation et carte des bibliothèques
- Catalogue des livres avec filtres instantanés côté client
- Fiche détaillée d’un livre avec localisation de la bibliothèque
- Inscription, connexion, déconnexion et gestion de session
- Emprunt d’un livre avec formulaire POST
- Confirmation d’emprunt
- Espace utilisateur avec profil et historique
- Espace administrateur avec dashboard, gestion des livres, emprunts, utilisateurs, bibliothèques et statistiques

## Structure du projet

```text
library-management-php-oop/
├── public/
│   ├── index.php
│   └── assets/
├── app/
│   ├── config/
│   ├── core/
│   ├── models/
│   ├── controllers/
│   └── views/
├── database/
│   └── db.sql
└── README.md
```

## Installation

1. Importez `database/db.sql` dans MySQL.
2. Ouvrez `app/config/Database.php` et vérifiez les identifiants MySQL :
   - base de données : `library_management`
   - utilisateur : `root`
   - mot de passe : vide par défaut
3. Configurez votre serveur local pour pointer vers le dossier `public/`.
4. Lancez le site avec :

```text
http://localhost/library-management-php-oop/public/index.php
```

## Avec Docker

Lancez la stack complète :

```text
docker compose up -d --build
```

- application PHP : `http://localhost:8090`
- phpMyAdmin : `http://localhost:8091`
- base MySQL : `localhost:3306`

Les identifiants Docker sont :

- MySQL root : `root` / `root`
- Base : `library_management`

## Comptes de démonstration

### Administrateur

- Email : `admin@bibliotheque.local`
- Mot de passe : `admin123`

Le compte administrateur est stocké dans la table `users` avec le rôle `admin`.

### Utilisateur de test

- Email : `marie.dupont@example.com`
- Mot de passe : `user123`

## OOP et MVC léger

- `Model.php` fournit la classe parent pour toutes les classes de données.
- `Controller.php` fournit la classe parent pour les contrôleurs.
- Chaque entité a sa propre classe : `Livre`, `Emprunt`, `Bibliotheque`, `User`.
- Les contrôleurs récupèrent les données depuis les modèles puis les transmettent aux vues.
- Les vues ne font qu’afficher les données.
- Le routage est simple et repose sur `public/index.php?page=...`.

## Utilisation de JavaScript

Le site utilise uniquement du JavaScript vanilla pour :

- filtrer les livres sans rechargement de page
- calculer la durée d’un emprunt
- valider les formulaires côté client
- afficher les statistiques sous forme de barres

Les données PHP sont injectées dans JavaScript avec `json_encode`.

## Utilisation de Leaflet

Leaflet est utilisé uniquement pour afficher les bibliothèques sur une carte.

- les coordonnées latitude/longitude sont stockées en MySQL
- PHP charge les bibliothèques
- les données sont injectées dans JavaScript avec `json_encode`
- Leaflet + OpenStreetMap affiche les marqueurs sans appeler d’API PHP

## Sécurité

- requêtes préparées PDO
- `password_hash` et `password_verify`
- sessions PHP
- `htmlspecialchars` via le helper `e()`
- formulaires `POST` pour les actions sensibles

## Remarque

Cette base est volontairement simple et beginner-friendly. Elle est pensée pour être lue facilement, modifiée vite et étendue module par module.
