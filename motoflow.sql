-- ============================================================
-- motoflow.sql — Script de création de la base de données
-- ============================================================
-- Pour utiliser ce fichier :
-- 1. Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Cliquer sur "Importer" en haut
-- 3. Choisir ce fichier et cliquer "Importer"
-- Tout sera créé automatiquement !
-- ============================================================

-- On crée la base de données si elle n'existe pas encore
CREATE DATABASE IF NOT EXISTS motoflow
    CHARACTER SET utf8mb4        -- Supporte les accents et emojis
    COLLATE utf8mb4_unicode_ci;  -- Tri et comparaison correcte des accents

-- On sélectionne cette base de données pour la suite
USE motoflow;

-- ============================================================
-- TABLE : utilisateurs
-- Stocke les comptes des clients et admins
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id         INT AUTO_INCREMENT PRIMARY KEY,  -- Identifiant unique, s'incrémente tout seul
    prenom     VARCHAR(100) NOT NULL,           -- Prénom de l'utilisateur
    nom        VARCHAR(100) NOT NULL,           -- Nom de l'utilisateur
    email      VARCHAR(150) NOT NULL UNIQUE,    -- Email unique (pas deux fois le même)
    mot_de_passe VARCHAR(255) NOT NULL,         -- Mot de passe hashé (jamais en clair !)
    telephone  VARCHAR(20) DEFAULT '',          -- Téléphone (optionnel)
    role       ENUM('client','admin') DEFAULT 'client', -- Rôle : client ou admin
    cree_le    TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création du compte
);

-- ============================================================
-- TABLE : motos
-- Stocke les motos disponibles à la vente
-- ============================================================
CREATE TABLE IF NOT EXISTS motos (
    id          INT AUTO_INCREMENT PRIMARY KEY, -- Identifiant unique
    marque      VARCHAR(100) NOT NULL,          -- Ex: Honda, Yamaha, Ducati
    modele      VARCHAR(150) NOT NULL,          -- Ex: CB500F, MT-07, Monster
    annee       INT NOT NULL,                   -- Année du modèle (ex: 2023)
    prix        DECIMAL(12,0) NOT NULL,         -- Prix en FCFA (ex: 4790000 FCFA, sans décimales)
    stock       INT DEFAULT 0,                  -- Nombre d'exemplaires disponibles
    cylindree   INT DEFAULT 0,                  -- Cylindrée en cm³ (ex: 500)
    puissance   INT DEFAULT 0,                  -- Puissance en chevaux (ex: 47)
    description TEXT,                           -- Description détaillée
    image       VARCHAR(255) DEFAULT '',        -- Nom du fichier image (ex: honda_cb500.jpg)
    actif       TINYINT(1) DEFAULT 1,           -- 1=visible, 0=masqué (soft delete)
    cree_le     TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date d'ajout
);

-- ============================================================
-- TABLE : panier
-- Stocke les motos que les clients veulent acheter
-- C'est une table temporaire, vidée après commande
-- ============================================================
CREATE TABLE IF NOT EXISTS panier (
    id              INT AUTO_INCREMENT PRIMARY KEY, -- Identifiant unique
    utilisateur_id  INT NOT NULL,                   -- Qui a ajouté (FK vers utilisateurs)
    moto_id         INT NOT NULL,                   -- Quelle moto (FK vers motos)
    quantite        INT DEFAULT 1,                  -- Combien d'exemplaires
    ajoute_le       TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Quand ajouté

    -- Relation : si l'utilisateur est supprimé, ses paniers sont supprimés aussi
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE : commandes
-- Stocke les commandes passées (en-tête de commande)
-- ============================================================
CREATE TABLE IF NOT EXISTS commandes (
    id              INT AUTO_INCREMENT PRIMARY KEY, -- Numéro de commande unique
    utilisateur_id  INT NOT NULL,                   -- Qui a commandé
    total           DECIMAL(14,0) NOT NULL,         -- Montant total en FCFA
    adresse         TEXT NOT NULL,                  -- Adresse de livraison
    statut          ENUM('en_attente','confirmee','expediee','livree','annulee')
                    DEFAULT 'en_attente',           -- Statut actuel de la commande
    cree_le         TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date de commande

    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE : commande_details
-- Stocke le détail de chaque commande (quelles motos, à quel prix)
-- On stocke le prix au moment de la commande car il peut changer
-- ============================================================
CREATE TABLE IF NOT EXISTS commande_details (
    id          INT AUTO_INCREMENT PRIMARY KEY, -- Identifiant unique
    commande_id INT NOT NULL,                   -- Quelle commande
    moto_id     INT NOT NULL,                   -- Quelle moto
    quantite    INT NOT NULL,                   -- Combien
    prix_unitaire DECIMAL(12,0) NOT NULL,       -- Prix au moment de la commande (FCFA)

    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- ============================================================
-- DONNÉES DE TEST
-- Des données pour tester l'application immédiatement
-- ============================================================

-- Compte admin : admin@motoflow.com / password123
-- Compte client : client@motoflow.com / password123
-- (Les mots de passe sont hashés avec bcrypt)
INSERT INTO utilisateurs (prenom, nom, email, mot_de_passe, role) VALUES
('Super', 'Admin',  'admin@motoflow.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Marie', 'Dupont', 'client@motoflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client'),
('Jean',  'Martin', 'jean@motoflow.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client');

-- Quelques motos de test (prix en FCFA)
INSERT INTO motos (marque, modele, annee, prix, stock, cylindree, puissance, description, image) VALUES
('Honda',   'CB500F',       2023, 4790000,  5, 471,  47, 'Moto idéale pour débuter et progresser. Légère, fiable et économique.', ''),
('Yamaha',  'MT-07',        2023, 5700000,  3, 689,  73, 'Roadster musclé avec un moteur bicylindre percutant. Sensation garantie.', ''),
('Kawasaki','Z650',         2022, 5250000,  4, 649,  68, 'Design agressif et performances au rendez-vous pour un tarif accessible.', ''),
('Ducati',  'Monster 797',  2022, 7870000,  2, 803,  73, 'L''icône italienne. Design inoubliable et plaisir de conduite unique.', ''),
('BMW',     'G310R',        2023, 3600000,  6, 313,  34, 'Parfaite pour la ville et les courtes distances. Maniable et légère.', ''),
('Suzuki',  'SV650',        2023, 4920000,  4, 645,  70, 'Le rapport qualité/prix imbattable. Moteur V-twin adoré des pilotes.', ''),
('KTM',     'Duke 390',     2023, 4130000,  5, 373,  43, 'Légère et agile, taillée pour le sport urbain et les petites routes.', ''),
('Honda',   'CB650R',       2023, 6230000,  2, 649,  95, 'Neo Sports Café : le style vintage avec la technologie moderne.', '');
