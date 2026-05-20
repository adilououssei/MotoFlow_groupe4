<?php
/* ============================================================
   deconnexion.php — Déconnexion de l'utilisateur
   ============================================================
   Ce fichier détruit la session PHP et redirige vers l'accueil.
   C'est le plus simple de tous les fichiers !
============================================================ */

// On démarre (ou reprend) la session pour pouvoir la détruire
session_start();

// On vide toutes les variables de session
// L'utilisateur n'est plus "connecté"
$_SESSION = [];

// On détruit le cookie de session dans le navigateur
// Ça assure que même le cookie est invalidé
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params(); // On récupère les paramètres du cookie
    setcookie(
        session_name(),    // Nom du cookie de session (PHPSESSID)
        '',                // Valeur vide
        time() - 42000,   // Date d'expiration dans le passé = supprime le cookie
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// On détruit la session côté serveur
session_destroy();

// On redirige vers la page de connexion avec un message
header('Location: connexion.php');
exit(); // Important : on arrête le script après la redirection
?>
