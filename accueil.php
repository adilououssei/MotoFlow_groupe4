<?php
/* ============================================================
   accueil.php — Page d'accueil de MotoFlow (VERSION STATIQUE)
   ============================================================
   Toutes les données sont en dur pour la démonstration
   Pas de connexion à la base de données
============================================================ */

// Pas de session, pas de base de données
// On définit juste des valeurs statiques

$titre_page = 'Accueil';

// Données statiques au lieu des requêtes SQL
$nb_motos = 24;
$nb_marques = 8;
$nb_clients = 156;

// Motos statiques pour la section nouveautés
$motos_recentes = [
    [
        'id' => 1,
        'marque' => 'Yamaha',
        'modele' => 'MT-07',
        'annee' => 2024,
        'cylindree' => 689,
        'puissance' => 74,
        'prix' => 7599000,
        'stock' => 5,
        'image' => '',
        'description' => 'La MT-07 est un roadster agile et puissant.'
    ],
    [
        'id' => 2,
        'marque' => 'Honda',
        'modele' => 'CBR 650R',
        'annee' => 2024,
        'cylindree' => 649,
        'puissance' => 95,
        'prix' => 8999000,
        'stock' => 3,
        'image' => '',
        'description' => 'Sportive polyvalente au look agressif.'
    ],
    [
        'id' => 3,
        'marque' => 'Kawasaki',
        'modele' => 'Z900',
        'annee' => 2023,
        'cylindree' => 948,
        'puissance' => 125,
        'prix' => 10499000,
        'stock' => 7,
        'image' => '',
        'description' => 'Roadster musclé à la puissance impressionnante.'
    ],
    [
        'id' => 4,
        'marque' => 'Suzuki',
        'modele' => 'GSX-8S',
        'annee' => 2024,
        'cylindree' => 776,
        'puissance' => 83,
        'prix' => 8699000,
        'stock' => 2,
        'image' => '',
        'description' => 'Nouveau roadster au design moderne.'
    ],
    [
        'id' => 5,
        'marque' => 'BMW',
        'modele' => 'R 1250 GS',
        'annee' => 2024,
        'cylindree' => 1254,
        'puissance' => 136,
        'prix' => 18999000,
        'stock' => 1,
        'image' => '',
        'description' => 'Le trail par excellence pour l\'aventure.'
    ],
    [
        'id' => 6,
        'marque' => 'Ducati',
        'modele' => 'Monster',
        'annee' => 2024,
        'cylindree' => 937,
        'puissance' => 111,
        'prix' => 13999000,
        'stock' => 4,
        'image' => '',
        'description' => 'Le mythe revisité avec un style italien.'
    ]
];

// Variable pour simuler un utilisateur non connecté ($est_connecte = false)
$est_connecte = false;

require_once 'header.php';
?>

<!-- SECTION HERO -->
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
        <div class="hero-boutons">
            <a href="catalogue.php" class="btn btn-principal">
                🏍️ Voir le catalogue
            </a>
            <?php if (!$est_connecte): ?>
                <a href="inscription.php" class="btn btn-secondaire" style="background:rgba(255,255,255,0.1); color:white; border-color:rgba(255,255,255,0.4);">
                    Créer un compte
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SECTION STATISTIQUES -->
<div class="conteneur">
    <div class="stats">
        <div class="stat-item fade-in">
            <span class="stat-chiffre" data-compteur="<?= $nb_motos ?>"><?= $nb_motos ?></span>
            <span class="stat-label">Motos disponibles</span>
        </div>
        <div class="stat-item fade-in">
            <span class="stat-chiffre" data-compteur="<?= $nb_marques ?>"><?= $nb_marques ?></span>
            <span class="stat-label">Marques représentées</span>
        </div>
        <div class="stat-item fade-in">
            <span class="stat-chiffre" data-compteur="<?= $nb_clients ?>"><?= $nb_clients ?></span>
            <span class="stat-label">Clients satisfaits</span>
        </div>
        <div class="stat-item fade-in">
            <span class="stat-chiffre">48h</span>
            <span class="stat-label">Délai de livraison</span>
        </div>
    </div>
</div>

<!-- SECTION NOUVEAUTÉS -->
<div class="conteneur" style="padding-bottom: 60px;">
    <h2 class="titre-section">Nouveautés</h2>
    <p class="sous-titre-section">Les dernières motos ajoutées à notre catalogue</p>

    <div class="grille-motos">
        <?php foreach ($motos_recentes as $moto): ?>
            <div class="carte-moto fade-in">
                <?php if (!empty($moto['image']) && file_exists('images/motos/' . $moto['image'])): ?>
                    <img class="carte-moto-image"
                         src="images/motos/<?= htmlspecialchars($moto['image']) ?>"
                         alt="<?= htmlspecialchars($moto['marque']) ?> <?= htmlspecialchars($moto['modele']) ?>">
                <?php else: ?>
                    <div class="carte-moto-placeholder">🏍️</div>
                <?php endif; ?>

                <div class="carte-moto-corps">
                    <div class="carte-moto-marque"><?= htmlspecialchars($moto['marque']) ?></div>
                    <div class="carte-moto-modele"><?= htmlspecialchars($moto['modele']) ?></div>
                    <div class="carte-moto-specs">
                        <span>📅 <?= $moto['annee'] ?></span>
                        <span>⚙️ <?= $moto['cylindree'] ?> cc</span>
                        <span>💪 <?= $moto['puissance'] ?> cv</span>
                    </div>
                    <?php if ($moto['stock'] > 3): ?>
                        <span class="badge-stock stock-dispo">✅ Disponible</span>
                    <?php elseif ($moto['stock'] > 0): ?>
                        <span class="badge-stock stock-faible">⚠️ Dernières unités</span>
                    <?php else: ?>
                        <span class="badge-stock stock-epuise">❌ Épuisé</span>
                    <?php endif; ?>
                </div>

                <div class="carte-moto-pied">
                    <span class="carte-moto-prix">
                        <?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA
                    </span>
                    <a href="detail.php?id=<?= $moto['id'] ?>" class="btn btn-principal btn-sm">
                        Voir
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="catalogue.php" class="btn btn-secondaire">
            Voir tout le catalogue →
        </a>
    </div>
</div>

<?php require_once 'footer.php'; ?>