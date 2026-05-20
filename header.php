<?php
/* ============================================================
   header.php — En-tête commun à toutes les pages
   ============================================================
   Ce fichier est inclus en haut de chaque page via :
   require_once 'header.php';
   Il affiche la balise <html>, le <head>, et la navbar.
   La variable $titre_page doit être définie AVANT l'inclusion.
   Exemple : $titre_page = "Catalogue"; require_once 'header.php';
============================================================ */

// On s'assure que les fichiers nécessaires sont chargés
require_once 'fonctions.php'; // Nos fonctions utilitaires
demarrer_session();            // Démarre ou reprend la session

// Valeur par défaut si $titre_page n'est pas définie
$titre_page = $titre_page ?? 'MotoFlow';

// Si l'utilisateur est connecté, on compte ses articles dans le panier
$nb_panier = 0;
if (est_connecte()) {
    require_once 'db.php'; // On inclut la connexion BD
    $nb_panier = compter_panier($pdo, $_SESSION['user_id']); // On compte
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Encodage des caractères (accents, emojis) -->
    <meta charset="UTF-8">
    <!-- Adapte l'affichage aux écrans mobiles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Titre de l'onglet du navigateur -->
    <title><?= propre($titre_page) ?> — MotoFlow</title>
    <!-- Notre feuille de style CSS -->
    <link rel="stylesheet" href="style.css">
    <!-- Police Google Fonts (Segoe UI n'est pas disponible sur tous les systèmes) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- ============================================================
     NAVBAR — Barre de navigation
     Elle s'affiche sur toutes les pages
============================================================ -->
<nav class="navbar">
    <div class="conteneur navbar-interieur">

        <!-- Logo MotoFlow (cliquable, renvoie vers l'accueil) -->
        <a href="accueil.php" class="navbar-logo">
            <!-- Le logo PNG qu'on a mis dans le dossier -->
            <img src="logo.png" alt="MotoFlow" onerror="this.style.display='none'">
            <!-- Nom de l'application en texte (affiché si logo absent) -->
            <span class="navbar-logo-texte">MotoFlow</span>
        </a>

        <!-- Bouton hamburger (visible seulement sur mobile) -->
        <button id="btnHamburger" style="
            display: none;          /* Caché sur desktop */
            background: none;       /* Pas de fond */
            border: none;           /* Pas de bordure */
            color: white;           /* Icône blanche */
            font-size: 1.5rem;      /* Grande icône */
            cursor: pointer;        /* Curseur pointer */
            padding: 5px;
        ">☰</button>

        <!-- Liens de navigation -->
        <ul class="navbar-menu" id="navbarMenu">
            <!-- Lien Accueil -->
            <li><a href="accueil.php"> Accueil</a></li>
            <!-- Lien Catalogue -->
            <li><a href="catalogue.php">Catalogue</a></li>

            <?php if (est_connecte()): ?>
                <!-- Liens visibles uniquement si connecté -->
                <li>
                    <a href="panier.php">
                         Panier
                        <?php if ($nb_panier > 0): ?>
                            <!-- Badge avec le nombre d'articles -->
                            <span class="badge-panier"><?= $nb_panier ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="commande.php"> Mes commandes</a></li>

                <?php if (est_admin()): ?>
                    <!-- Lien visible uniquement si admin -->
                    <li><a href="admin.php"> Admin</a></li>
                <?php endif; ?>

                <!-- Avatar et nom de l'utilisateur connecté -->
                <li>
                    <div class="nav-user">
                        <!-- Initiale de l'utilisateur dans un rond -->
                        <div class="nav-avatar">
                            <?= strtoupper(substr($_SESSION['prenom'], 0, 1)) ?>
                        </div>
                        <span class="nav-user-nom"><?= propre($_SESSION['prenom']) ?></span>
                        <a href="deconnexion.php" style="color: rgba(255,255,255,0.5); font-size:0.85rem; text-decoration:none;" title="Se déconnecter">⬚</a>
                    </div>
                </li>

            <?php else: ?>
                <!-- Boutons connexion/inscription si non connecté -->
                <li><a href="connexion.php">Connexion</a></li>
                <li><a href="inscription.php" class="btn-nav-connexion">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Affichage du message flash s'il y en a un -->
<div class="conteneur" style="padding-top: 10px;">
    <?php afficher_message(); ?>
</div>

<!-- Le contenu de la page commencera ici -->
