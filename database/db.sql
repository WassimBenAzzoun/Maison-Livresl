-- Library management project database
-- Database name: library_management

CREATE DATABASE IF NOT EXISTS library_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE library_management;

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = 'utf8mb4_unicode_ci';

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS emprunts;
DROP TABLE IF EXISTS livres;
DROP TABLE IF EXISTS bibliotheques;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    membership_type ENUM('none', 'monthly', 'yearly') NOT NULL DEFAULT 'none',
    membership_paid_at DATE DEFAULT NULL,
    membership_expires_at DATE DEFAULT NULL,
    membership_branch_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bibliotheques (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(120) NOT NULL,
    telephone VARCHAR(30) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD CONSTRAINT fk_users_membership_branch
        FOREIGN KEY (membership_branch_id) REFERENCES bibliotheques(id)
        ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE livres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bibliotheque_id INT UNSIGNED NULL,
    titre VARCHAR(180) NOT NULL,
    auteur VARCHAR(150) NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    annee_publication INT NOT NULL,
    description TEXT DEFAULT NULL,
    couverture VARCHAR(255) DEFAULT NULL,
    total_exemplaires INT UNSIGNED NOT NULL DEFAULT 1,
    available_exemplaires INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_livres_bibliotheques
        FOREIGN KEY (bibliotheque_id) REFERENCES bibliotheques(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE emprunts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    livre_id INT UNSIGNED NULL,
    bibliotheque_id INT UNSIGNED NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    borrow_date DATE NOT NULL,
    return_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'returned') NOT NULL DEFAULT 'pending',
    livre_titre VARCHAR(180) NOT NULL,
    livre_categorie VARCHAR(100) NOT NULL,
    bibliotheque_nom VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_emprunts_users
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_emprunts_livres
        FOREIGN KEY (livre_id) REFERENCES livres(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_emprunts_bibliotheques
        FOREIGN KEY (bibliotheque_id) REFERENCES bibliotheques(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, full_name, email, phone, password, address, status, role, membership_type, membership_paid_at, membership_expires_at, membership_branch_id, created_at) VALUES
(1, 'Marie Dupont', 'marie.dupont@example.com', '+216 20 111 222', '$2b$12$C5pXSKJxT2tHuWAwEV2.I.xLz6PpYH4eRO8lwh2OdctQqeyIaqOhG', '12 rue de la République, Tunis', 'active', 'user', 'monthly', '2026-05-01', '2026-06-01', 1, NOW()),
(2, 'Nader Ben Ali', 'nader.benali@example.com', '+216 21 333 444', '$2b$12$C5pXSKJxT2tHuWAwEV2.I.xLz6PpYH4eRO8lwh2OdctQqeyIaqOhG', '18 avenue Habib Bourguiba, Sfax', 'active', 'user', 'yearly', '2026-04-01', '2027-04-01', 2, NOW()),
(3, 'Sarra Jaziri', 'sarra.jaziri@example.com', '+216 22 555 666', '$2b$12$C5pXSKJxT2tHuWAwEV2.I.xLz6PpYH4eRO8lwh2OdctQqeyIaqOhG', '5 rue des Oliviers, Sousse', 'inactive', 'user', 'none', NULL, NULL, NULL, NOW()),
(4, 'Administrateur', 'admin@bibliotheque.local', '+216 00 000 000', '$2b$12$nf2i7d5p/zPJ78s7jxcT.uDkRdBlu6tlmovA7PLMri1MjBDKDWYcC', 'Bureau principal', 'active', 'admin', 'none', NULL, NULL, NULL, NOW());

INSERT INTO bibliotheques (id, nom, adresse, ville, telephone, description, latitude, longitude, created_at) VALUES
(1, 'Bibliothèque Centrale Tunis', '1 Avenue de la Liberté', 'Tunis', '+216 71 123 456', 'Succursale principale au cœur de la capitale.', 36.8063890, 10.1816670, NOW()),
(2, 'Médiathèque Sfax', '25 Rue des Arts', 'Sfax', '+216 74 987 654', 'Espace moderne pour la lecture et l\'étude.', 34.7405560, 10.7602780, NOW()),
(3, 'Bibliothèque Sousse', '14 Boulevard de la Corniche', 'Sousse', '+216 73 222 111', 'Bibliothèque de quartier proche du centre historique.', 35.8255560, 10.6347220, NOW());

INSERT INTO livres (id, bibliotheque_id, titre, auteur, categorie, annee_publication, description, couverture, total_exemplaires, available_exemplaires, created_at) VALUES
(1, 1, 'Le Petit Prince', 'Antoine de Saint-Exupéry', 'Classique', 1943, 'Un récit poétique sur l\'amitié, l\'enfance et la découverte du monde.', 'assets/images/book-placeholder.svg', 5, 3, NOW()),
(2, 1, 'L\'étranger', 'Albert Camus', 'Roman', 1942, 'Un grand texte de la littérature française moderne.', 'assets/images/book-placeholder.svg', 4, 4, NOW()),
(3, 2, 'Dom Juan', 'Molière', 'Théâtre', 1665, 'Une pièce emblématique du théâtre classique.', 'assets/images/book-placeholder.svg', 3, 2, NOW()),
(4, 2, 'Clean Code', 'Robert C. Martin', 'Informatique', 2008, 'Un guide de référence pour écrire du code lisible et maintenable.', 'assets/images/book-placeholder.svg', 6, 5, NOW()),
(5, 3, '1984', 'George Orwell', 'Dystopie', 1949, 'Un roman culte sur la surveillance et le contrôle social.', 'assets/images/book-placeholder.svg', 2, 1, NOW());

INSERT INTO emprunts (id, user_id, livre_id, bibliotheque_id, full_name, email, phone, borrow_date, return_date, status, livre_titre, livre_categorie, bibliotheque_nom, created_at) VALUES
(1, 1, 1, 1, 'Marie Dupont', 'marie.dupont@example.com', '+216 20 111 222', '2026-05-01', '2026-05-15', 'pending', 'Le Petit Prince', 'Classique', 'Bibliothèque Centrale Tunis', NOW()),
(2, 2, 4, 2, 'Nader Ben Ali', 'nader.benali@example.com', '+216 21 333 444', '2026-04-18', '2026-05-02', 'confirmed', 'Clean Code', 'Informatique', 'Médiathèque Sfax', NOW()),
(3, 1, 5, 3, 'Marie Dupont', 'marie.dupont@example.com', '+216 20 111 222', '2026-03-11', '2026-03-25', 'returned', '1984', 'Dystopie', 'Bibliothèque Sousse', NOW());
