# Maison des Livres

Application web simple de gestion de bibliothèque réalisée en PHP, MySQL, HTML/CSS et JavaScript vanilla.

## Présentation

Le projet est organisé en pages PHP indépendantes. Chaque écran possède son propre fichier dans `public/` et charge uniquement ce dont il a besoin. Cette structure est volontairement simple pour un projet étudiant et facile à présenter.

## Technologies

- PHP orienté objet pour les modèles
- MySQL avec PDO
- HTML5 / CSS3
- JavaScript vanilla
- Leaflet.js + OpenStreetMap pour la carte

Les comptes utilisent une seule table `users` avec un champ `role` (`user` ou `admin`).

## Fonctionnalités

- page d’accueil avec présentation et carte des points de service
- catalogue des livres avec filtres côté client
- fiche détaillée d’un livre
- inscription, connexion, déconnexion et sessions
- emprunt de livre avec formulaire POST
- confirmation d’emprunt
- espace utilisateur avec profil et emprunts
- espace administrateur avec livres, emprunts, utilisateurs, points de service, adhésions et statistiques

## Structure simplifiée

```text
library-management-php-oop/
├── public/
│   ├── home.php
│   ├── books.php
│   ├── book.php
│   ├── login.php
│   ├── register.php
│   ├── profile.php
│   ├── borrow.php
│   ├── confirmation.php
│   ├── admin-dashboard.php
│   ├── admin-books.php
│   ├── admin-borrowings.php
│   ├── admin-users.php
│   ├── admin-branches.php
│   ├── admin-statistics.php
│   └── assets/
├── app/
│   ├── config/
│   ├── core/
│   └── models/
├── database/
│   └── db.sql
└── README.md
```

## Installation locale

1. Importer `database/db.sql` dans MySQL.
2. Vérifier les identifiants de base de données dans `app/config/Database.php`.
3. Lancer le serveur local en pointant vers le dossier `public/`.
4. Ouvrir directement les pages du projet, par exemple :

```text
http://localhost:8050/home.php
```

## Avec Docker

Lancer la stack :

```text
docker compose up -d --build
```

- application web : `http://localhost:8050`
- phpMyAdmin : `http://localhost:8091`
- MySQL : `localhost:3308`

## Comptes de démonstration

### Administrateur

- Email : `admin@bibliotheque.local`
- Mot de passe : `admin123`

### Utilisateur

- Email : `marie.dupont@example.com`
- Mot de passe : `user123`

## Organisation du code

- `app/core/Model.php` fournit la classe parente des modèles.
- `app/core/helpers.php` contient les fonctions utilitaires partagées.
- Chaque entité a sa propre classe dans `app/models/`.
- Les pages PHP dans `public/` affichent elles-mêmes le HTML et appellent les modèles directement.
- `public/index.php` sert seulement de redirection vers `home.php`.

## JavaScript

Le JavaScript vanilla sert à :

- filtrer les livres sans rechargement
- calculer la durée d’un emprunt
- remplir automatiquement les dates d’adhésion
- afficher les statistiques simples

Les données PHP sont injectées dans JavaScript avec `json_encode`.

## Carte

Leaflet affiche les points de service sur une carte à partir des coordonnées stockées en base de données. PHP charge les données, puis les envoie à JavaScript pour affichage.

## Sécurité

- requêtes préparées PDO
- `password_hash` et `password_verify`
- sessions PHP
- `htmlspecialchars` via le helper `e()`
- formulaires `POST` pour les actions sensibles

## Remarque

Ce projet est volontairement simple, lisible et beginner-friendly afin de convenir à un rendu académique.
