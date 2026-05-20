<?php
/* ============================================================
   accueil.php — Page d'accueil de MotoFlow
   ============================================================
   Cette page affiche :
   - Une bannière (hero) avec un message de bienvenue
   - Des statistiques (nombre de motos, marques...)
   - Les motos les plus récentes (les 6 dernières)
============================================================ */

// On inclut la connexion à la base de données
require_once 'db.php';

// On inclut nos fonctions utilitaires
require_once 'fonctions.php';

// On démarre la session
demarrer_session();

// Titre de la page (affiché dans l'onglet du navigateur)
$titre_page = 'Accueil';

/* ----------------------------------------------------------
   RÉCUPÉRATION DES DONNÉES DEPUIS LA BASE DE DONNÉES
   On fait des requêtes SQL pour obtenir les infos à afficher
---------------------------------------------------------- */

// Compter le nombre total de motos disponibles
// COUNT(*) compte toutes les lignes qui correspondent au WHERE
$stmt = $pdo->query("SELECT COUNT(*) FROM motos WHERE actif = 1");
$nb_motos = $stmt->fetchColumn(); // fetchColumn() retourne juste la première valeur

// Compter le nombre de marques différentes
// DISTINCT évite de compter deux fois la même marque
$stmt = $pdo->query("SELECT COUNT(DISTINCT marque) FROM motos WHERE actif = 1");
$nb_marques = $stmt->fetchColumn();

// Compter le nombre de clients inscrits
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'client'");
$nb_clients = $stmt->fetchColumn();

// Récupérer les 6 motos les plus récentes pour la section "Nouveautés"
// ORDER BY cree_le DESC = du plus récent au plus ancien
// LIMIT 6 = seulement 6 résultats
$stmt = $pdo->query("SELECT * FROM motos WHERE actif = 1 ORDER BY cree_le DESC LIMIT 6");
$motos_recentes = $stmt->fetchAll(); // fetchAll() retourne un tableau de toutes les lignes

// Affichage de l'en-tête (navbar)
require_once 'header.php';
?>

<!-- ============================================================
     SECTION HERO — Grande bannière de bienvenue
============================================================ -->
<section class="hero">
    <div class="conteneur">
        <h1 class="hero-titre">
            Trouvez votre moto<br>
            <span>de rêve</span>
        </h1>
        <p class="hero-sous-titre">
            Plus de <?= $nb_motos ?> motos disponibles.
            Trouvez celle qui vous correspond parmi les meilleures marques.
        </p>
        <!-- Boutons d'action principaux -->
        <div class="hero-boutons">
            <!-- Bouton principal vers le catalogue -->
            <a href="catalogue.php" class="btn btn-principal">
                 Voir le catalogue
            </a>
            <?php if (!est_connecte()): ?>
                <!-- Si pas connecté, on invite à s'inscrire -->
                <a href="inscription.php" class="btn btn-secondaire" style="background:rgba(255,255,255,0.1); color:white; border-color:rgba(255,255,255,0.4);">
                    Créer un compte
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION STATISTIQUES
============================================================ -->
<div class="conteneur">
    <div class="stats">
        <!-- Stat 1 : Nombre de motos -->
        <div class="stat-item fade-in">
            <!-- data-compteur permet à JS d'animer le chiffre -->
            <span class="stat-chiffre" data-compteur="<?= $nb_motos ?>"><?= $nb_motos ?></span>
            <span class="stat-label">Motos disponibles</span>
        </div>

        <!-- Stat 2 : Nombre de marques -->
        <div class="stat-item fade-in">
            <span class="stat-chiffre" data-compteur="<?= $nb_marques ?>"><?= $nb_marques ?></span>
            <span class="stat-label">Marques représentées</span>
        </div>

        <!-- Stat 3 : Clients inscrits -->
        <div class="stat-item fade-in">
            <span class="stat-chiffre" data-compteur="<?= $nb_clients ?>"><?= $nb_clients ?></span>
            <span class="stat-label">Clients satisfaits</span>
        </div>

        <!-- Stat 4 : Livraison (valeur fixe) -->
        <div class="stat-item fade-in">
            <span class="stat-chiffre">48h</span>
            <span class="stat-label">Délai de livraison</span>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION NOUVEAUTÉS
============================================================ -->
<div class="conteneur" style="padding-bottom: 60px;">
    <h2 class="titre-section">Nouveautés</h2>
    <p class="sous-titre-section">Les dernières motos ajoutées à notre catalogue</p>

    <!-- Grille des cartes moto -->
    <div class="grille-motos">
        <?php foreach ($motos_recentes as $moto): ?>
            <!-- Une carte par moto -->
            <div class="carte-moto fade-in">

                <!-- Image ou icône si pas d'image -->
                <?php if (!empty($moto['image']) && file_exists('images/motos/' . $moto['image'])): ?>
                    <!-- On vérifie que le fichier image existe vraiment -->
                    <img class="carte-moto-image"
                         src="images/motos/<?= propre($moto['image']) ?>"
                         alt="<?= propre($moto['marque']) ?> <?= propre($moto['modele']) ?>">
                <?php else: ?>
                    <!-- Placeholder avec une icône si pas d'image -->
                    <div class="carte-moto-placeholder">🏍️</div>
                <?php endif; ?>

                <!-- Corps de la carte -->
                <div class="carte-moto-corps">
                    <!-- Marque en rouge en haut -->
                    <div class="carte-moto-marque"><?= propre($moto['marque']) ?></div>
                    <!-- Modèle en gros -->
                    <div class="carte-moto-modele"><?= propre($moto['modele']) ?></div>
                    <!-- Spécifications techniques -->
                    <div class="carte-moto-specs">
                        <span>annee: <?= $moto['annee'] ?></span>
                        <span>cylindre: <?= $moto['cylindree'] ?> cc</span>
                        <span>puissance: <?= $moto['puissance'] ?> cv</span>
                    </div>
                    <!-- Badge de disponibilité -->
                    <?php if ($moto['stock'] > 3): ?>
                        <span class="badge-stock stock-dispo"> Disponible</span>
                    <?php elseif ($moto['stock'] > 0): ?>
                        <span class="badge-stock stock-faible"> Dernières unités</span>
                    <?php else: ?>
                        <span class="badge-stock stock-epuise"> Épuisé</span>
                    <?php endif; ?>
                </div>

                <!-- Pied de carte : prix et bouton -->
                <div class="carte-moto-pied">
                    <!-- Prix formaté avec séparateur de milliers -->
                    <span class="carte-moto-prix">
                        <?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA
                    </span>
                    <!-- Lien vers la page de détail -->
                    <a href="detail.php?id=<?= $moto['id'] ?>" class="btn btn-principal btn-sm">
                        Voir
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bouton pour voir tout le catalogue -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="catalogue.php" class="btn btn-secondaire">
            Voir tout le catalogue →
        </a>
    </div>
</div>

<?php require_once 'footer.php'; ?>
