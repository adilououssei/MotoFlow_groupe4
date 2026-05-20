<?php
/* ============================================================
   db.php — Connexion à la base de données
   ============================================================
   Ce fichier crée la connexion à MySQL via PDO.
   On l'inclut dans chaque page qui a besoin de la base de données.
   Utilisation : require_once 'db.php';  puis on utilise $pdo
============================================================ */

// Paramètres de connexion à la base de données
$hote     = 'localhost';   // Adresse du serveur MySQL (toujours localhost avec XAMPP)
$nom_bd   = 'motoflow';    // Nom de la base de données
$login    = 'root';        // Identifiant MySQL (root par défaut avec XAMPP)
$mdp      = '';            // Mot de passe MySQL (vide par défaut avec XAMPP)

try {
    // On crée la connexion PDO avec le charset UTF-8 pour les accents
    $pdo = new PDO(
        "mysql:host=$hote;dbname=$nom_bd;charset=utf8",  // Chaîne de connexion
        $login,  // Identifiant
        $mdp     // Mot de passe
    );

    // On configure PDO pour qu'il lance une exception en cas d'erreur SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // On configure PDO pour retourner les résultats en tableaux associatifs
    // (ex: $moto['prix'] au lieu de $moto[3])
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si la connexion échoue, on affiche un message d'erreur et on arrête tout
    die('<p style="color:red;font-family:Arial;padding:20px;">
         Erreur de connexion à la base de données : ' . $e->getMessage() . '
         <br>Vérifie que XAMPP est démarré et que la base de données existe.
         </p>');
}
?>
