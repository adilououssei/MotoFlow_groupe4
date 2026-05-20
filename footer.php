<?php
/* ============================================================
   footer.php — Pied de page commun à toutes les pages
   ============================================================
   Inclus en bas de chaque page via : require_once 'footer.php';
============================================================ */
?>
<!-- Notre script JavaScript chargé EN BAS de page -->
<!-- On le met en bas pour ne pas ralentir l'affichage de la page -->
<script src="script.js"></script>

<footer>
    <p>
        <!-- Année automatique avec PHP -->
        &copy; <?= date('Y') ?> MotoFlow — La fluidité dans l'achat de motos
        &nbsp;|&nbsp;
        <a href="catalogue.php">Catalogue</a>
        &nbsp;|&nbsp;
        <a href="connexion.php">Connexion</a>
    </p>
</footer>

</body>
</html>
