<?php
/* ============================================================
   header.php — En-tête commun à toutes les pages (VERSION STATIQUE)
   ============================================================
   Version sans session, avec un utilisateur non connecté par défaut
============================================================ */

$titre_page = $titre_page ?? 'MotoFlow';
$est_connecte = false;
$nb_panier = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre_page) ?> — MotoFlow</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="conteneur navbar-interieur">
        <a href="accueil.php" class="navbar-logo">
            <img src="logo.png" alt="MotoFlow" onerror="this.style.display='none'">
            <span class="navbar-logo-texte">MotoFlow</span>
        </a>

        <button id="btnHamburger" style="
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
        ">☰</button>

        <ul class="navbar-menu" id="navbarMenu">
            <li><a href="accueil.php">🏠 Accueil</a></li>
            <li><a href="catalogue.php">🏍️ Catalogue</a></li>

            <?php if ($est_connecte): ?>
                <li><a href="panier.php">🛒 Panier</a></li>
                <li><a href="commande.php">📦 Mes commandes</a></li>
                <li><a href="deconnexion.php">🔓 Déconnexion</a></li>
            <?php else: ?>
                <li><a href="connexion.php">Connexion</a></li>
                <li><a href="inscription.php" class="btn-nav-connexion">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="conteneur" style="padding-top: 10px;">
    <?php
    // Message flash (ne s'affichera pas en statique)
    if (isset($_SESSION['flash_message'])) {
        echo '<div class="message message-' . $_SESSION['flash_type'] . '">' 
             . htmlspecialchars($_SESSION['flash_message']) . '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
    ?>
</div>