<?php
/* ============================================================
   index.php — Page d'entrée du projet
   ============================================================
   Quand on ouvre http://localhost/moto-flow/
   PHP cherche index.php en premier.
   On redirige simplement vers accueil.php.
============================================================ */

header('Location: accueil.php'); // Redirection vers l'accueil
exit();
?>
