<?php
/* ============================================================
   catalogue.php — Liste de toutes les motos (VERSION STATIQUE)
   ============================================================
   Toutes les motos sont en dur dans le code
   Les filtres ne fonctionnent pas vraiment (juste pour l'UI)
============================================================ */

require_once 'header.php';

// Données statiques - toutes les motos du catalogue
$toutes_motos = [
    [
        'id' => 1, 'marque' => 'Yamaha', 'modele' => 'MT-07', 'annee' => 2024,
        'cylindree' => 689, 'puissance' => 74, 'prix' => 7599000, 'stock' => 5,
        'image' => '', 'description' => 'Roadster agile'
    ],
    [
        'id' => 2, 'marque' => 'Honda', 'modele' => 'CBR 650R', 'annee' => 2024,
        'cylindree' => 649, 'puissance' => 95, 'prix' => 8999000, 'stock' => 3,
        'image' => '', 'description' => 'Sportive polyvalente'
    ],
    [
        'id' => 3, 'marque' => 'Kawasaki', 'modele' => 'Z900', 'annee' => 2023,
        'cylindree' => 948, 'puissance' => 125, 'prix' => 10499000, 'stock' => 7,
        'image' => '', 'description' => 'Roadster musclé'
    ],
    [
        'id' => 4, 'marque' => 'Suzuki', 'modele' => 'GSX-8S', 'annee' => 2024,
        'cylindree' => 776, 'puissance' => 83, 'prix' => 8699000, 'stock' => 2,
        'image' => '', 'description' => 'Design moderne'
    ],
    [
        'id' => 5, 'marque' => 'BMW', 'modele' => 'R 1250 GS', 'annee' => 2024,
        'cylindree' => 1254, 'puissance' => 136, 'prix' => 18999000, 'stock' => 1,
        'image' => '', 'description' => 'Trail d\'aventure'
    ],
    [
        'id' => 6, 'marque' => 'Ducati', 'modele' => 'Monster', 'annee' => 2024,
        'cylindree' => 937, 'puissance' => 111, 'prix' => 13999000, 'stock' => 4,
        'image' => '', 'description' => 'Style italien'
    ],
    [
        'id' => 7, 'marque' => 'KTM', 'modele' => '390 Duke', 'annee' => 2024,
        'cylindree' => 373, 'puissance' => 44, 'prix' => 5499000, 'stock' => 8,
        'image' => '', 'description' => 'Naked nerveuse'
    ],
    [
        'id' => 8, 'marque' => 'Triumph', 'modele' => 'Street Triple', 'annee' => 2023,
        'cylindree' => 765, 'puissance' => 118, 'prix' => 12499000, 'stock' => 2,
        'image' => '', 'description' => 'Performance britannique'
    ]
];

$nb_resultats = count($toutes_motos);

$marques = ['Yamaha', 'Honda', 'Kawasaki', 'Suzuki', 'BMW', 'Ducati', 'KTM', 'Triumph'];

$titre_page = 'Catalogue';
?>

<div class="conteneur" style="padding-top: 25px;">

    <div class="flex-entre" style="margin-bottom: 20px;">
        <div>
            <h1 class="titre-section">🏍️ Catalogue</h1>
            <p class="sous-titre-section">
                <?= $nb_resultats ?> moto<?= $nb_resultats > 1 ? 's' : '' ?> trouvée<?= $nb_resultats > 1 ? 's' : '' ?>
            </p>
        </div>
    </div>

    <div class="catalogue-mise-en-page">

        <!-- SIDEBAR FILTRES (non fonctionnels pour la version statique) -->
        <aside>
            <form action="" method="GET" class="filtres">
                <div class="filtres-titre">🔍 Filtres</div>

                <div class="filtre-groupe">
                    <label for="recherche">Rechercher</label>
                    <input type="text" id="recherche" name="recherche"
                        placeholder="Honda, Yamaha..."
                        class="champ-input">
                </div>

                <div class="filtre-groupe">
                    <label for="marque">Marque</label>
                    <select id="marque" name="marque" class="champ-input">
                        <option value="">Toutes les marques</option>
                        <?php foreach ($marques as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filtre-groupe">
                    <label for="prix_min">Prix minimum (FCFA)</label>
                    <input type="number" id="prix_min" name="prix_min"
                        placeholder="0" min="0" step="100" class="champ-input">
                </div>

                <div class="filtre-groupe">
                    <label for="prix_max">Prix maximum (FCFA)</label>
                    <input type="number" id="prix_max" name="prix_max"
                        placeholder="Sans limite" min="0" step="100" class="champ-input">
                </div>

                <div class="filtre-groupe">
                    <label for="tri">Trier par</label>
                    <select id="tri" name="tri" class="champ-input">
                        <option value="recent">Plus récent</option>
                        <option value="prix_asc">Prix croissant</option>
                        <option value="prix_desc">Prix décroissant</option>
                        <option value="annee_desc">Année (récent)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-principal btn-plein" style="margin-bottom:8px;">
                    🔍 Appliquer
                </button>
                <a href="catalogue.php" class="btn btn-secondaire btn-plein" style="justify-content:center; font-size:0.9rem;">
                    ✕ Réinitialiser
                </a>
            </form>
        </aside>

        <!-- GRILLE DES MOTOS -->
        <div>
            <div class="grille-motos">
                <?php foreach ($toutes_motos as $moto): ?>
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
                                <span class="badge-stock stock-dispo">✅ En stock</span>
                            <?php elseif ($moto['stock'] > 0): ?>
                                <span class="badge-stock stock-faible">⚠️ Stock limité (<?= $moto['stock'] ?>)</span>
                            <?php else: ?>
                                <span class="badge-stock stock-epuise">❌ Épuisé</span>
                            <?php endif; ?>
                        </div>

                        <div class="carte-moto-pied">
                            <span class="carte-moto-prix">
                                <?= number_format($moto['prix'], 0, ',', ' ') ?> FCFA
                            </span>
                            <a href="detail.php?id=<?= $moto['id'] ?>" class="btn btn-principal btn-sm">
                                Voir →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>